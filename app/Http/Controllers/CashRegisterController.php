<?php

namespace App\Http\Controllers;

use App\Models\CashMovement;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Caja: cuánto efectivo debería haber físicamente en el mesón ahora mismo
 * (saldo acumulado, no solo del día) más el detalle día a día de qué entró
 * y qué salió — pagos en efectivo de reservas + movimientos manuales de
 * caja chica (ingresos y egresos que no vienen de una reserva).
 */
class CashRegisterController extends Controller
{
    public function show(?string $date = null): View
    {
        $day = $date ? Carbon::parse($date, 'America/Santiago')->startOfDay() : now('America/Santiago')->startOfDay();

        $cashMethodId = PaymentMethod::where('code', 'efectivo')->value('id');

        // Saldo en caja: todo el efectivo cobrado en reservas, más los
        // ingresos manuales, menos los egresos manuales — histórico
        // completo, porque la caja no se vacía sola cada medianoche.
        $totalCashFromSales = (int) Payment::where('payment_method_id', $cashMethodId)
            ->where('status', 'aprobado')->sum('amount');
        $totalIngresos = (int) CashMovement::where('type', 'ingreso')->sum('amount');
        $totalEgresos = (int) CashMovement::where('type', 'egreso')->sum('amount');
        $saldoEnCaja = $totalCashFromSales + $totalIngresos - $totalEgresos;

        // Detalle del día elegido, para el desglose día a día.
        $dayPayments = Payment::with(['booking.room', 'booking.customer'])
            ->where('payment_method_id', $cashMethodId)
            ->where('status', 'aprobado')
            ->whereBetween('created_at', [$day->copy(), $day->copy()->endOfDay()])
            ->get();
        $dayMovements = CashMovement::whereBetween('created_at', [$day->copy(), $day->copy()->endOfDay()])->get();

        $timeline = $dayPayments->map(fn (Payment $p) => [
            'time' => $p->created_at->timezone('America/Santiago'),
            'kind' => 'ingreso',
            'label' => 'Pago en efectivo — '.($p->booking?->room?->name ?? '—').' · '.($p->booking?->customer?->name ?? '—'),
            'code' => $p->booking?->code,
            'amount' => $p->amount,
        ])->concat($dayMovements->map(fn (CashMovement $m) => [
            'time' => $m->created_at->timezone('America/Santiago'),
            'kind' => $m->type,
            'label' => ($m->type === 'ingreso' ? 'Ingreso manual' : 'Egreso').($m->description ? ' — '.$m->description : ''),
            'code' => null,
            'amount' => $m->amount,
        ]))->sortByDesc('time')->values();

        $dayCashIn = (int) $dayPayments->sum('amount') + (int) $dayMovements->where('type', 'ingreso')->sum('amount');
        $dayCashOut = (int) $dayMovements->where('type', 'egreso')->sum('amount');

        return view('cash.show', [
            'day' => $day,
            'saldoEnCaja' => $saldoEnCaja,
            'totalCashFromSales' => $totalCashFromSales,
            'totalIngresos' => $totalIngresos,
            'totalEgresos' => $totalEgresos,
            'timeline' => $timeline,
            'dayCashIn' => $dayCashIn,
            'dayCashOut' => $dayCashOut,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:ingreso,egreso'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        CashMovement::create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $label = $validated['type'] === 'ingreso' ? 'Ingreso' : 'Egreso';

        return redirect()->route('cash.show')
            ->with('status', $label.' de $'.number_format($validated['amount'], 0, ',', '.').' registrado en caja.');
    }
}
