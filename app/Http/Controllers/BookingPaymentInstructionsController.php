<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

/**
 * Registra cuándo se le mandaron al cliente los datos de pago (botón de
 * WhatsApp / copiar en reservations/show) -- antes no quedaba ningún rastro
 * de si ya se había hecho ese seguimiento, así que no había forma de
 * distinguir "recién llegó" de "ya se le avisó, falta que transfiera" al
 * mirar el panel de pendientes o al cambiar de turno.
 */
class BookingPaymentInstructionsController extends Controller
{
    public function store(string $code): JsonResponse
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        if (! $booking->payment_instructions_sent_at) {
            $booking->update(['payment_instructions_sent_at' => now()]);
            AuditLog::record(auth()->id(), 'reserva.instrucciones_pago_enviadas', 'Booking', $booking->id, [], ['payment_instructions_sent_at' => $booking->payment_instructions_sent_at]);
        }

        return response()->json(['ok' => true]);
    }
}
