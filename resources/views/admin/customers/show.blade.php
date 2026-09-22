@extends('admin.layout')
@section('title', $customer->name)
@section('content')
    <style>
        .seg-banner { border-radius:10px; padding:16px 18px; margin-bottom:18px; }
        .seg-banner.nuevo { background:#16232b; border:1px solid #2c4a5e; }
        .seg-banner.frecuente { background:#1c2f1c; border:1px solid #2e5a2e; }
        .seg-banner.ocasional { background:#2a2410; border:1px solid #6b5a1e; }
        .seg-banner.esporadico { background:#3a1c22; border:1px solid #7a2d2d; }
        .seg-banner .seg-label { font-size:11px; text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px; opacity:.8; }
        .seg-banner .seg-title { font-size:16px; font-weight:700; margin-bottom:6px; }
        .seg-banner .seg-why { font-size:13px; color:#ccc; line-height:1.5; }
        .seg-stats { display:flex; gap:20px; margin-top:12px; flex-wrap:wrap; }
        .seg-stats div span { display:block; font-size:11px; color:#999; text-transform:uppercase; letter-spacing:.04em; }
        .seg-stats div b { font-family: ui-monospace, monospace; font-size:17px; }
        .row { display:flex; justify-content:space-between; padding: 7px 0; font-size: 14.5px; gap:12px; }
        .row .muted { color:#999; flex:none; }
        .sec-label { font-size: 11px; color:#888; text-transform:uppercase; letter-spacing:.05em; margin: 22px 0 10px; }
        .sec-label:first-of-type { margin-top:0; }
        a.row-card { display:block; background:#1c1c1c; border:1px solid #333; border-radius: 10px; padding: 12px 16px; margin-bottom: 10px; text-decoration:none; color:#eee; }
        a.row-card.now { border-color:#ff7918; background:#241419; }
        a.row-card:hover { border-color:#666; }
        .row1 { display:flex; justify-content:space-between; align-items:baseline; }
        .code { font-family: ui-monospace, monospace; color:#ff7918; font-size: 12.5px; }
        .when { font-weight:600; font-size:14.5px; }
        .meta { color:#999; font-size:13px; margin-top:4px; }
        .empty { color:#777; font-size:14px; padding: 4px 0 20px; }
    </style>

    <p class="sub" style="margin-bottom:6px;">
        <a class="link" href="#" onclick="if (window.history.length > 1) { history.back(); return false; }">← Volver</a>
        · <a class="link" href="{{ route('admin.customers.index') }}">Todos los clientes</a>
    </p>
    <h1>{{ $customer->name }}</h1>
    <p class="sub">
        {{ $customer->phone_e164 }}{{ $customer->email ? ' · '.$customer->email : '' }}
        @if ($customer->documentNumber())
            · {{ $customer->document_type === 'pasaporte' ? 'Pasaporte' : 'RUT' }} {{ $customer->documentNumber() }}
        @endif
        {{ $customer->nationality ? ' · '.$customer->nationality : '' }}
        {{ $customer->age() !== null ? ' · '.$customer->age().' años' : '' }}
    </p>

    <details style="margin-bottom:18px;">
        <summary class="link" style="cursor:pointer;">Editar datos del cliente</summary>
        <div class="card" style="margin-top:10px;">
            <form method="POST" action="{{ route('admin.customers.update', $customer) }}">
                @csrf @method('PUT')
                <div class="row2">
                    <div>
                        <label>Nombre</label>
                        <input name="name" value="{{ $customer->name }}" maxlength="150" required>
                    </div>
                    <div>
                        <label>Correo</label>
                        <input type="email" name="email" value="{{ $customer->email }}" maxlength="255" placeholder="cliente@correo.cl">
                    </div>
                    <div>
                        <label>Tipo de documento</label>
                        <select name="document_type">
                            <option value="">— Sin definir</option>
                            <option value="rut" @selected($customer->document_type === 'rut')>RUT</option>
                            <option value="pasaporte" @selected($customer->document_type === 'pasaporte')>Pasaporte</option>
                        </select>
                    </div>
                    <div>
                        <label>RUT</label>
                        <input name="rut" value="{{ $customer->rut }}" maxlength="20">
                    </div>
                    <div>
                        <label>N° de pasaporte</label>
                        <input name="passport_number" value="{{ $customer->passport_number }}" maxlength="30">
                    </div>
                    <div>
                        <label>Nacionalidad</label>
                        <input name="nationality" value="{{ $customer->nationality }}" maxlength="60">
                    </div>
                    <div>
                        <label>Fecha de nacimiento</label>
                        <input type="date" name="birth_date" value="{{ $customer->birth_date?->toDateString() }}">
                    </div>
                </div>
                <p class="sub" style="margin:10px 0 0;">El teléfono no se edita acá — es la identidad del cliente. Al guardar, se sincroniza el correo/nombre con GHL de inmediato.</p>
                <div class="actions" style="margin-top:12px;"><button class="btn" type="submit">Guardar cambios</button></div>
            </form>
        </div>
    </details>

    @if ($monthlyVisits >= 3)
        <div style="background:#211d10; border:1px solid #7a6a1e; color:#f0d98a; border-radius:10px; padding:12px 16px; margin-bottom:14px; font-weight:600; font-size:14px;">
            ⭐ Recurrente del mes — {{ $monthlyVisits }} reservas en {{ ucfirst(now()->locale('es')->isoFormat('MMMM')) }}
        </div>
    @endif

    <div class="seg-banner {{ $segment['type'] }}">
        <div class="seg-label">Segmento histórico</div>
        <div class="seg-title">{{ $segment['label'] }}</div>
        <div class="seg-why">
            @switch($segment['type'])
                @case('nuevo')
                    Todavía no acumula estadías previas — es su primera vez o la próxima será su primera vez completada.
                    @break
                @case('frecuente')
                    Vino {{ $segment['total_stays'] }} veces, en promedio cada {{ $segment['avg_interval_days'] }} días, y su última visita fue hace {{ $segment['days_since_last'] }} días. Buen candidato para beneficios de fidelización.
                    @break
                @case('ocasional')
                    Vino {{ $segment['total_stays'] }} veces, con un promedio de {{ $segment['avg_interval_days'] }} días entre visitas. Viene, pero sin un patrón muy seguido todavía.
                    @break
                @case('esporadico')
                    Tiene {{ $segment['total_stays'] }} estadías en su historial, pero la última fue hace {{ $segment['days_since_last'] }} días — se desconectó. Podría valer la pena reactivarlo con una promoción.
                    @break
            @endswitch
        </div>
        <div class="seg-stats">
            <div><span>Estadías</span><b>{{ $segment['total_stays'] }}</b></div>
            <div><span>Última visita</span><b>{{ $segment['last_visit_at']?->timezone('America/Santiago')->format('d/m/Y') ?? '—' }}</b></div>
            @if ($segment['avg_interval_days'])
                <div><span>Cada</span><b>{{ $segment['avg_interval_days'] }}d</b></div>
            @endif
        </div>
    </div>

    <div class="sec-label">Hoy y próximas</div>
    @forelse ($upcoming as $booking)
        @php $isNow = $booking->starts_at <= $now && $booking->ends_at > $now; @endphp
        <a class="row-card {{ $isNow ? 'now' : '' }}" href="{{ route('reservations.show', $booking->code) }}">
            <div class="row1">
                <span class="when">{{ ucfirst($booking->starts_at->timezone('America/Santiago')->locale('es')->isoFormat('dddd D/MM, H:mm')) }}</span>
                <span class="pill">{{ $isNow ? 'OCUPANDO AHORA' : $booking->booking_status }}</span>
            </div>
            <div class="meta">{{ $booking->room->name }} ({{ $booking->room->category->name }}) · {{ $booking->duration_minutes / 60 }} h · <span class="code">{{ $booking->code }}</span></div>
        </a>
    @empty
        <p class="empty">No tiene reservas próximas.</p>
    @endforelse

    @if ($past->isNotEmpty())
        <div class="sec-label">Historial</div>
        @foreach ($past as $booking)
            <a class="row-card" href="{{ route('reservations.show', $booking->code) }}" style="opacity:.7;">
                <div class="row1">
                    <span class="when">{{ ucfirst($booking->starts_at->timezone('America/Santiago')->locale('es')->isoFormat('dddd D/MM, H:mm')) }}</span>
                    <span class="pill">{{ $booking->booking_status }}</span>
                </div>
                <div class="meta">{{ $booking->room->name }} ({{ $booking->room->category->name }}) · {{ $booking->duration_minutes / 60 }} h · <span class="code">{{ $booking->code }}</span></div>
            </a>
        @endforeach
    @endif
@endsection
