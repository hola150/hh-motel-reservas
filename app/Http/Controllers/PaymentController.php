<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentExceedsBalanceException;
use App\Models\Booking;
use App\Models\PaymentMethod;
use App\Services\Booking\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Request $request, string $code): View
    {
        $booking = Booking::with(['room.category', 'customer', 'payments.paymentMethod'])
            ->where('code', $code)->firstOrFail();

        $methods = PaymentMethod::where('is_active', true)->orderBy('name')->get();
        $after = $request->query('after') === 'board' ? 'board' : null;

        // Recién creada con abono: se sugiere ese monto en vez del saldo
        // completo — recepción puede ajustarlo si corresponde otra cosa.
        $suggestedAmount = $booking->deposit_amount > 0
            ? min((int) $booking->deposit_amount, $booking->balanceDue())
            : $booking->balanceDue();

        return view('payments.create', [
            'booking' => $booking,
            'methods' => $methods,
            'after' => $after,
            'suggestedAmount' => $suggestedAmount,
        ]);
    }

    public function store(Request $request, string $code, PaymentService $paymentService): RedirectResponse
    {
        $booking = Booking::where('code', $code)->firstOrFail();

        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'integer', 'min:1', 'max:'.max($booking->balanceDue(), 1)],
            'external_id' => ['nullable', 'string', 'max:255'],
            // Basta con uno de los dos — no siempre hay voucher Y boleta para
            // el mismo pago (ej. una transferencia solo trae comprobante).
            'voucher_number' => ['nullable', 'required_without:receipt_number', 'string', 'max:100'],
            'receipt_number' => ['nullable', 'required_without:voucher_number', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:255'],
            'after' => ['nullable', 'in:board'],
        ], [
            'amount.max' => 'El monto no puede superar el saldo pendiente ($'.number_format($booking->balanceDue(), 0, ',', '.').').',
            'voucher_number.required_without' => 'Ingresa el N° de voucher o el N° de boleta (al menos uno de los dos).',
            'receipt_number.required_without' => 'Ingresa el N° de voucher o el N° de boleta (al menos uno de los dos).',
        ]);

        $method = PaymentMethod::findOrFail($validated['payment_method_id']);

        // Si recepción no anota una referencia propia (ej. N° de operación de
        // una transferencia), generamos una nosotros -- así todo pago manual
        // queda igual de rastreable, sin depender de que alguien la tipee.
        $externalId = $validated['external_id'] ?: 'REC-'.now()->format('ymdHis').'-'.strtoupper(Str::random(4));

        try {
            $paymentService->register(
                $booking,
                $method,
                (int) $validated['amount'],
                $externalId,
                $validated['notes'] ?? null,
                auth()->id(),
                $validated['voucher_number'] ?? null,
                $validated['receipt_number'] ?? null,
            );
        } catch (PaymentExceedsBalanceException $e) {
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }

        if (($validated['after'] ?? null) === 'board') {
            return redirect()->route('rooms.board')
                ->with('status', 'Pago registrado — '.$booking->code.' · '.$method->name.' · $'.number_format($validated['amount'], 0, ',', '.').'.');
        }

        return redirect()->route('reservations.show', $booking->code);
    }
}
