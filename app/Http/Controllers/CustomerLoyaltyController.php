<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerLoyaltyController extends Controller
{
    public function store(Request $request, string $code): RedirectResponse
    {
        $booking = Booking::with('customer')->where('code', $code)->firstOrFail();
        $customer = $booking->customer;

        $validated = $request->validate([
            'loyalty_card_number' => ['nullable', 'string', 'max:50'],
        ]);

        $old = $customer->only(['has_loyalty_card', 'loyalty_card_number']);

        $customer->update([
            'has_loyalty_card' => true,
            'loyalty_card_number' => $validated['loyalty_card_number'] ?: $customer->loyalty_card_number,
            'loyalty_card_added_at' => now(),
        ]);

        AuditLog::record(auth()->id(), 'cliente.tarjeta_fidelizacion_entregar', 'Customer', $customer->id, $old, $customer->only(['has_loyalty_card', 'loyalty_card_number']));

        return redirect()->route('reservations.show', $booking->code)->with('status', 'Tarjeta de fidelización registrada.');
    }
}
