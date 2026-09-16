<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingCancelController extends Controller
{
    public function store(Request $request, string $code): RedirectResponse
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        if (in_array($booking->booking_status, ['CANCELADA', 'EXPIRADA', 'NO_SHOW', 'FINALIZADA'], true)) {
            return redirect()->route('reservations.show', $booking->code)
                ->withErrors(['booking' => 'Esta reserva ya está cerrada — no se puede cancelar.']);
        }

        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $booking->only(['booking_status']);
        $booking->update([
            'booking_status' => 'CANCELADA',
            'notes' => trim(($booking->notes ? $booking->notes."\n" : '')."Cancelada: ".($validated['reason'] ?: 'sin motivo indicado')),
        ]);

        AuditLog::record(auth()->id(), 'reserva.cancelar', 'Booking', $booking->id, $old, $booking->fresh()->only(['booking_status', 'notes']));

        return redirect()->route('reservations.show', $booking->code)->with('status', 'Reserva cancelada.');
    }
}
