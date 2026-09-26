<?php

namespace App\Http\Controllers;

use App\Models\ExternalReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Recibe el trigger de automatización "Reviews Received" de GHL (Workflow →
 * Acción "Webhook") cada vez que llega una reseña nueva de Google/Facebook a
 * la ficha conectada -- así el sistema se entera en tiempo real, sin tener
 * que consultar nada (GHL no expone una API pública documentada de lectura
 * para Reputation Management, solo este trigger + webhook saliente).
 *
 * El formato exacto del body no está documentado públicamente, así que se
 * prueban varios nombres de campo posibles y SIEMPRE se guarda el payload
 * completo en raw_payload -- si algún campo no calza, no se pierde nada, se
 * ajusta el parseo después mirando lo ya guardado.
 */
class GhlReviewWebhookController extends Controller
{
    public function store(Request $request, string $token): JsonResponse
    {
        if (! hash_equals((string) config('services.ghl.review_webhook_token'), $token)) {
            abort(404);
        }

        $payload = $request->all();

        $externalId = $this->pick($payload, ['reviewId', 'id', 'review_id']);

        // La acción "AI Reply" de GHL corre DESPUÉS del trigger en el mismo
        // Workflow -- si el webhook está configurado para disparar de nuevo
        // ahí, llega un segundo POST para la misma reseña, esta vez con la
        // respuesta. Solo se actualizan los campos que sí vinieron en ESTE
        // POST (array_filter saca los null) para no borrar con vacío lo que
        // ya se había guardado en el primer POST.
        $fields = array_filter([
            'source' => $this->pick($payload, ['source', 'platform', 'reviewSource', 'review_source']),
            'rating' => $this->normalizeRating($this->pick($payload, ['rating', 'reviewRating', 'starRating', 'review_rating'])),
            'comment' => $this->pick($payload, ['comment', 'reviewBody', 'review_body', 'text', 'review']),
            'reply' => $this->pick($payload, ['reply', 'aiReply', 'ai_reply', 'replyText', 'reply_text', 'response']),
            'reviewer_name' => $this->pick($payload, ['reviewerName', 'reviewer_name', 'authorName', 'author_name', 'name']),
            'reviewed_at' => $this->pick($payload, ['createTime', 'createdAt', 'created_at', 'reviewDate', 'review_date', 'date']),
        ], fn ($value) => $value !== null);
        $fields['raw_payload'] = $payload;

        ExternalReview::updateOrCreate(
            ['external_id' => $externalId ?? 'sin-id-'.md5(json_encode($payload))],
            $fields
        );

        return response()->json(['ok' => true]);
    }

    private function pick(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }

    /**
     * Google usa "ONE".."FIVE" en su propia API -- GHL podría reenviarlo
     * así tal cual en vez de como número.
     */
    private function normalizeRating(?string $rating): ?int
    {
        if ($rating === null) {
            return null;
        }
        if (is_numeric($rating)) {
            return (int) round((float) $rating);
        }

        return match (strtoupper($rating)) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => null,
        };
    }
}
