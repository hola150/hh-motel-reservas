@php $room = $entry['room']; $status = $entry['status']; $offer = ($roomOffers ?? [])[$room->id] ?? null; $needsMaintenance = $room->latestInspection && $room->latestInspection->needs_maintenance; @endphp
@php $cardTarget = ($status['current_booking'] ?? null) ? route('reservations.show', $status['current_booking']->code) : route('rooms.bookings', $room); @endphp
<div class="card cat-{{ Str::slug($room->category->name) }} {{ $needsMaintenance ? 'needs-maintenance' : '' }} {{ (($status['eta_minutes'] ?? 0) < 0 && ($status['current_booking'] ?? null)) ? 'overdue-room' : '' }}" data-href="{{ $cardTarget }}">
    <div class="card-head">
        <b>{{ $room->name }}</b>
        @if ($offer)
            <span class="offer-badge" title="{{ $offer['label'] }}">{{ $offer['tag'] }}</span>
        @endif
        @if ($needsMaintenance)
            <span class="maintenance-badge" title="{{ $room->latestInspection->notes }}">Mantención</span>
        @endif
    </div>
    <div class="cat">Categoría {{ $room->category->name }}</div>
    @if ($room->furniture->where('pivot.quantity', '>', 0)->isNotEmpty())
        <div class="equipment-icons" title="Equipamiento de la habitación">
            @foreach ($room->furniture->where('pivot.quantity', '>', 0)->take(5) as $equipment)
                <span title="{{ $equipment->name }}" aria-label="{{ $equipment->name }}">{{ $equipment->icon ?? '✦' }}</span>
            @endforeach
        </div>
    @endif

    @if ($room->operational_status === 'aseo')
        <span class="pill pill-aseo">EN ASEO</span>
    @elseif ($room->operational_status !== 'activa')
        <span class="pill pill-{{ $room->operational_status }}">{{ strtoupper($room->operational_status) }}</span>
    @elseif ($status['occupancy'] === 'ocupada' && $status['current_booking'] && ! $status['current_booking']->checked_in_at)
        <span class="pill pill-reservada">RESERVADA — SIN CHECK-IN</span>
    @elseif ($status['occupancy'] === 'ocupada')
        <span class="pill pill-ocupada">OCUPADA</span>
    @else
        <span class="pill pill-libre">LIBRE</span>
        @if ($status['next_booking'])
            <span class="pill pill-upcoming">{{ $status['next_booking']->starts_at->timezone('America/Santiago')->format('H:i') }}</span>
        @endif
    @endif

    @if ($room->operational_status === 'aseo')
        <div class="eta">Esperando aseo — no vuelve a disponibles hasta que recepción la marque</div>
        <form method="POST" action="{{ route('rooms.aseo_ready', $room) }}" class="aseo-ready-form">
            @csrf
            <select name="cleaned_by" class="aseo-cleaner-input" required>
                <option value="" selected disabled>¿Quién hizo el aseo?</option>
                @foreach (\App\Support\AseoStaff::NAMES as $name)
                    <option value="{{ $name }}">{{ $name }}</option>
                @endforeach
            </select>
            <button type="submit" class="aseo-ready-btn">Marcar aseo listo — reactivar</button>
        </form>
    @elseif ($room->operational_status !== 'activa')
        @if ($room->operational_note)
            <div class="eta">{{ $room->operational_note }}</div>
        @endif
    @else
        @if ($status['occupancy'] === 'ocupada' && $status['current_booking'])
            @if ($status['eta_minutes'] < 0)
                <div class="eta overtime">Pasó su horario hace {{ \App\Support\TimeFormat::minutes($status['eta_minutes']) }}</div>
            @else
                <div class="eta">Libera en {{ \App\Support\TimeFormat::minutes($status['eta_minutes']) }}</div>
            @endif
            <div class="code">{{ $status['current_booking']->code }}</div>
            <a class="consumo-btn" href="{{ route('reservations.show', $status['current_booking']->code) }}#consumo">+ Agregar consumo →</a>
            @if ($status['current_booking']->checked_in_at)
                <a class="checkout-btn" href="{{ route('bookings.finalize.show', $status['current_booking']->code) }}">Finalizar / Check-out →</a>
            @else
                <a class="checkin-btn" href="{{ route('bookings.checkin.show', $status['current_booking']->code) }}">Falta check-in — marcar ahora</a>
            @endif
        @elseif ($status['next_booking'])
            <div class="eta">Próxima: {{ $status['next_booking']->starts_at->timezone('America/Santiago')->format('H:i') }}</div>
        @else
            <div class="eta">Disponible ahora</div>
        @endif

        @if ($status['occupancy'] === 'libre')
            <a class="reserve-btn" href="{{ route('reservations.create', ['room_id' => $room->id]) }}">Reservar →</a>
        @endif
    @endif

    @if ($status['upcoming_count'] > 0)
        <a class="bookings-btn has-bookings" href="{{ route('rooms.bookings', $room) }}">
            {{ $status['upcoming_count'] }} {{ Str::plural('reserva', $status['upcoming_count']) }} →
        </a>
    @else
        <a class="bookings-btn empty" href="{{ route('rooms.bookings', $room) }}">Sin reservas próximas</a>
    @endif

    <details>
        <summary>Cambiar estado</summary>
        <form method="POST" action="{{ route('rooms.status', $room) }}" onsubmit="return this.operational_status.value !== '__aseo' || confirm('¿Mandar {{ $room->name }} a aseo? Queda fuera de disponibles hasta que la marques como activa.');">
            @csrf
            <select name="operational_status">
                <option value="activa" @selected($room->operational_status === 'activa') @disabled($room->operational_status === 'aseo')>Activa{{ $room->operational_status === 'aseo' ? ' — primero marcar aseo listo' : '' }}</option>
                <option value="mantencion" @selected($room->operational_status === 'mantencion')>En mantención</option>
                <option value="inactiva" @selected($room->operational_status === 'inactiva')>Inactiva</option>
                @if ($room->operational_status === 'aseo')
                    <option value="aseo" selected>En aseo</option>
                @elseif ($status['occupancy'] === 'libre')
                    <option value="__aseo">Aseo</option>
                @endif
            </select>
            <button type="submit">Guardar</button>
        </form>
    </details>
</div>
