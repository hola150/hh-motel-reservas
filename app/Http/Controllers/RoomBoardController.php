<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\OperationalSetting;
use App\Models\Room;
use App\Models\RoomCategory;
use App\Models\Staff;
use App\Services\Booking\RoomBoardService;
use App\Services\Pricing\RateRuleResolver;
use App\Support\TimeFormat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoomBoardController extends Controller
{
    public function index(RoomBoardService $board, RateRuleResolver $rates): View
    {
        $entries = collect($board->board());

        $isProxima = fn (array $e) => $e['status']['occupancy'] === 'libre' && $e['status']['imminent'];

        // Los interruptores de piso+ala / categoría viven en el panel de
        // administración (Categorías) -- acá solo se aplica su efecto,
        // sacando de "Disponibles" lo que corresponda. No es responsabilidad
        // de quien está en el tablero prender o apagar nada de esto.
        $setting = OperationalSetting::current();
        $disabledCategoryIds = RoomCategory::where('is_active', false)->pluck('id');

        $disponibles = $entries->filter(
            fn (array $e) => $e['room']->operational_status === 'activa' && $e['status']['occupancy'] === 'libre' && ! $isProxima($e)
        )->reject(
            fn (array $e) => $disabledCategoryIds->contains($e['room']->room_category_id)
                || ! $e['room']->isFloorWingEnabled($setting)
        );

        $grouped = [
            'ocupadas' => $entries->filter(
                fn (array $e) => $e['room']->operational_status === 'activa' && $e['status']['occupancy'] === 'ocupada'
            )->values(),
            'en_aseo' => $entries->filter(
                fn (array $e) => $e['room']->operational_status === 'aseo'
            )->values(),
            'disponibles' => $disponibles->values(),
            'proximas' => $entries->filter(
                fn (array $e) => $e['room']->operational_status === 'activa' && $isProxima($e)
            )->sortBy(fn (array $e) => $e['status']['minutes_until_next'])->values(),
            'fuera_de_servicio' => $entries->filter(
                fn (array $e) => ! in_array($e['room']->operational_status, ['activa', 'aseo'], true)
            )->values(),
        ];

        $now = now();
        $tariff = $rates->currentStatus($now);
        $nextOpening = $tariff ? null : $rates->nextOpening($now);

        return view('rooms.board', [
            'grouped' => $grouped,
            'roomOffers' => $this->activeOffersByRoom($entries, $now),
            'showTariff' => true,
            'tariff' => $tariff,
            'closesLabel' => $tariff ? $this->formatCountdown($now, $tariff['closes_at']) : null,
            'nextOpeningLabel' => $nextOpening ? $this->formatCountdown($now, $nextOpening) : null,
            'cleaningStaff' => $this->cleaningStaffNames(),
            'dailySummary' => $this->dailySummary(),
        ]);
    }

    /**
     * Resumen esencial del día para monitorear en el propio tablero, sin
     * tener que entrar a Ventas del día -- mismo criterio que esa pantalla
     * (reservas cuya estadía empieza hoy, sin canceladas): cuántos Playrooms
     * se vendieron y por cuánto, y cuántos extras se consumieron y por cuánto.
     */
    private function dailySummary(): array
    {
        $day = now('America/Santiago')->startOfDay();
        $dayEnd = $day->copy()->endOfDay();

        $bookings = Booking::with('addons')
            ->whereBetween('starts_at', [$day->copy(), $dayEnd])
            ->where('booking_status', '!=', 'CANCELADA')
            ->get();

        $addons = $bookings->flatMap->addons;

        return [
            'rooms_count' => $bookings->count(),
            'rooms_revenue' => (int) $bookings->sum('price_final'),
            'extras_count' => (int) $addons->sum('quantity'),
            'extras_revenue' => (int) $addons->sum('amount'),
        ];
    }

    /**
     * Para cada habitación, la mejor oferta vigente ahora (mayor % o el
     * precio fijo más bajo). Sale al tablero como badge "OFERTA".
     *
     * @return array<int, array{label: string, tag: string}>
     */
    private function activeOffersByRoom(\Illuminate\Support\Collection $entries, \Carbon\Carbon $now): array
    {
        $offers = Coupon::offers()->where('is_active', true)
            ->with(['rooms:id', 'roomCategories:id'])
            ->get();

        if ($offers->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($entries as $entry) {
            $room = $entry['room'];
            $live = $offers->filter(fn (Coupon $o) => $o->offerLiveFor($room, $now));
            if ($live->isEmpty()) {
                continue;
            }

            $pct = $live->where('discount_type', 'percentage')->max('discount_value');
            $tag = $pct ? 'DESCUENTO '.$pct.'%' : 'OFERTA';
            $result[$room->id] = ['label' => $live->first()->internal_name, 'tag' => $tag];
        }

        return $result;
    }

    private function formatCountdown(\Carbon\Carbon $now, \Carbon\Carbon $target): string
    {
        return TimeFormat::minutes((int) max(0, $now->diffInMinutes($target, false)));
    }

    /**
     * Resumen de todas las reservas activas del tablero — las que están
     * ocupando ahora y las que vienen más adelante, cruzando todas las
     * habitaciones — para no tener que entrar habitación por habitación a
     * buscarlas.
     */
    public function upcoming(): View
    {
        $now = now();

        $bookings = Booking::with(['room.category', 'customer'])
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA', 'NO_SHOW', 'FINALIZADA'])
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        return view('rooms.upcoming', ['bookings' => $bookings, 'now' => $now]);
    }

    public function bookings(Room $room): View
    {
        $now = now();

        // Terminal = ya terminó de verdad, sin importar si la hora agendada
        // de salida todavía no llega -- un check-out anticipado (FINALIZADA
        // antes de la hora) o un no-show/cancelación no deberían seguir
        // apareciendo en "Hoy y próximas" solo porque ends_at todavía no pasó.
        $terminalStatuses = ['CANCELADA', 'EXPIRADA', 'NO_SHOW', 'FINALIZADA'];

        $upcoming = $room->bookings()
            ->with('customer')
            ->whereNotIn('booking_status', $terminalStatuses)
            ->where('ends_at', '>=', $now)
            ->orderBy('starts_at')
            ->get();

        $past = $room->bookings()
            ->with('customer')
            ->where(function ($query) use ($terminalStatuses, $now) {
                $query->whereIn('booking_status', $terminalStatuses)
                    ->orWhere('ends_at', '<', $now);
            })
            ->orderByDesc('starts_at')
            ->limit(10)
            ->get();

        return view('rooms.bookings', ['room' => $room->load('category'), 'upcoming' => $upcoming, 'past' => $past, 'now' => $now]);
    }

    /**
     * "__aseo" es una opción del mismo <select> de estado — manda la
     * habitación a aseo (operational_status = 'aseo'), que queda fija hasta
     * que recepción la reactive a mano, igual que mantención o inactiva.
     */
    public function updateStatus(Request $request, Room $room): RedirectResponse
    {
        if ($request->input('operational_status') === '__aseo') {
            return $this->sendToAseo($room);
        }

        if ($room->operational_status === 'aseo' && $request->input('operational_status') === 'activa') {
            return back()->withErrors(['room' => 'La habitación debe marcarse como “aseo listo” antes de volver a activa.']);
        }

        $validated = $request->validate([
            'operational_status' => ['required', 'in:activa,mantencion,inactiva,aseo'],
            'operational_note' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $room->only(['operational_status', 'operational_note']);
        $room->update($validated);

        \App\Models\AuditLog::record(auth()->id(), 'habitacion.estado_operativo', 'Room', $room->id, $old, $validated);

        return redirect()->route('rooms.board');
    }

    /**
     * "Marcar aseo listo" — la mucama terminó y recepción reactiva la
     * habitación. Único camino de vuelta a disponibles después de un
     * check-out (o de un envío a aseo manual).
     */
    /**
     * Nombres del personal de mucamas activo, para el selector de "¿quién
     * hizo el aseo?" -- antes era una lista fija (App\Support\AseoStaff)
     * porque no existía un padrón real; ahora sale directo de /admin/personal.
     */
    private function cleaningStaffNames(): \Illuminate\Support\Collection
    {
        return Staff::where('role', 'Mucama')->where('is_active', true)->orderBy('name')->pluck('name');
    }

    public function markAseoReady(Request $request, Room $room): RedirectResponse
    {
        $names = $this->cleaningStaffNames();

        $validated = $request->validate([
            'cleaned_by' => ['required', 'string', Rule::in($names)],
        ], [
            'cleaned_by.required' => 'Falta indicar quién hizo el aseo.',
            'cleaned_by.in' => 'Elegí una persona de aseo válida.',
        ]);

        $old = $room->only(['operational_status']);
        $room->update(['operational_status' => 'activa', 'aseo_override_at' => now()]);

        // "Quién hizo el aseo" queda en el propio registro de auditoría de
        // este evento -- no hace falta una tabla aparte para la huella.
        \App\Models\AuditLog::record(auth()->id(), 'habitacion.aseo_listo', 'Room', $room->id, $old, [
            ...$room->fresh()->only(['operational_status']),
            'cleaned_by' => $validated['cleaned_by'],
        ]);

        return redirect()->route('rooms.board');
    }

    private function sendToAseo(Room $room): RedirectResponse
    {
        $old = $room->only(['operational_status']);
        $room->update(['operational_status' => 'aseo', 'aseo_started_at' => now(), 'aseo_override_at' => null]);

        \App\Models\AuditLog::record(auth()->id(), 'habitacion.enviar_aseo', 'Room', $room->id, $old, ['operational_status' => 'aseo']);

        return redirect()->route('rooms.board');
    }
}
