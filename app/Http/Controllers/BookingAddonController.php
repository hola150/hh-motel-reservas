<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Models\Booking;
use App\Models\Combo;
use App\Models\Product;
use App\Services\Booking\ConsumptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookingAddonController extends Controller
{
    public function store(Request $request, string $code, ConsumptionService $consumption): RedirectResponse
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'item' => ['nullable', 'string'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'integer', 'min:1', 'max:200000'],
        ], [
            'quantity.max' => 'Máximo 20 unidades por línea — si son más, agrégalo en dos veces.',
            'amount.max' => 'Ese monto parece demasiado alto para un ítem de consumo — revísalo.',
        ]);

        try {
            [$type, $id] = array_pad(explode(':', $validated['item'] ?? '', 2), 2, null);
            $quantity = (int) ($validated['quantity'] ?? 1);

            if ($type === 'combo' && $id) {
                $consumption->addCombo($booking, Combo::findOrFail($id), $quantity, auth()->id());
            } elseif ($type === 'product' && $id) {
                $consumption->addProduct($booking, Product::findOrFail($id), $quantity, auth()->id());
            } else {
                $request->validate(['description' => ['required'], 'amount' => ['required']]);
                $consumption->addCustom($booking, $validated['description'], (int) $validated['amount'], auth()->id());
            }
        } catch (InsufficientStockException $e) {
            return back()->withErrors(['booking' => $e->getMessage()]);
        }

        return redirect()->route('reservations.show', $booking->code);
    }
}
