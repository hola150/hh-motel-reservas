<?php

namespace App\Http\Controllers;

use App\Models\RoomCategory;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Número de WhatsApp del motel para el botón "Reservar" -- la reserva
     * real la sigue creando recepción, este catálogo es solo para que el
     * cliente vea las habitaciones y escriba.
     */
    private const WHATSAPP_NUMBER = '56977683108';

    public function index(): View
    {
        $categories = RoomCategory::where('is_active', true)
            ->orderBy('display_order')
            ->with([
                'rooms',
                'rateRulePrices' => fn ($q) => $q->whereHas('rateRule', fn ($r) => $r->where('is_active', true))->with('rateRule'),
            ])
            ->get()
            ->map(function (RoomCategory $category) {
                $photos = $category->rooms->flatMap(fn ($room) => $room->photos ?? [])->unique()->values();
                $videos = $category->rooms->flatMap(fn ($room) => $room->videos ?? [])->unique()->values();

                $prices = $category->rateRulePrices
                    ->groupBy('duration_minutes')
                    ->map(fn ($rows, $duration) => [
                        'duration' => (int) $duration,
                        'hh' => $rows->first(fn ($p) => $p->rateRule->name === 'HH')?->price,
                        'hot' => $rows->first(fn ($p) => $p->rateRule->name === 'HOT')?->price,
                    ])
                    ->sortBy('duration')
                    ->values();

                return [
                    'category' => $category,
                    'photos' => $photos,
                    'videos' => $videos,
                    'prices' => $prices,
                    'whatsappUrl' => 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode("Hola! Quiero reservar una habitación {$category->name} en HH Motel."),
                ];
            });

        return view('catalog.index', [
            'categories' => $categories,
            'whatsappUrl' => 'https://wa.me/'.self::WHATSAPP_NUMBER.'?text='.rawurlencode('Hola! Quiero reservar una habitación en HH Motel.'),
        ]);
    }
}
