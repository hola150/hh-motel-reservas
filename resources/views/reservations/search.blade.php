<!doctype html>
<html lang="es" style="background:#111;color:#eee">
<head>
    <meta charset="utf-8"><meta name="color-scheme" content="dark">
    <title>Buscar reserva — HH Motel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; }
        .page-inner { max-width: 600px; margin: 0; padding: 24px 24px 60px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .sub { color:#999; font-size: 13px; margin-bottom: 20px; }
        form { display:flex; gap:8px; margin-bottom: 22px; }
        input { flex:1; background:#1c1c1c; border:1px solid #333; color:#fff; padding:11px 14px; border-radius:8px; font-size:15px; }
        button { background:#ff7918; color:#fff; border:none; padding:0 20px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; }
        .card { background:#1c1c1c; border:1px solid #333; border-radius: 10px; padding: 14px 16px; margin-bottom: 12px; text-decoration:none; display:block; color:#eee; }
        .card:hover { border-color:#ff7918; }
        .row1 { display:flex; justify-content:space-between; align-items:baseline; }
        .code { font-family: ui-monospace, monospace; color:#ff7918; font-size: 13px; }
        .pill { display:inline-block; font-size:10.5px; font-family: ui-monospace, monospace; padding:2px 8px; border-radius:20px; background:#2a2a2a; color:#ccc; }
        .meta { color:#999; font-size:13px; margin-top:4px; }
        .empty { color:#777; font-size:14px; }
        .seg-pill { display:inline-block; font-size:10px; text-transform:uppercase; letter-spacing:.03em; padding:2px 8px; border-radius:20px; }
        .seg-nuevo { background:#1c2f3a; color:#7fbcdc; }
        .seg-frecuente { background:#1c3a2a; color:#6fd39a; }
        .seg-ocasional { background:#3a331c; color:#e8c76f; }
        .seg-esporadico { background:#3a1c22; color:#e88a9a; }
        .cust-card { background:#1c1c1c; border:1px solid #333; border-radius:10px; padding:14px 16px; margin-bottom:16px; }
        .cust-card.hot { border-color:#7a6a1e; background:#211d10; }
        .cust-card .cc-name { font-size:15px; font-weight:700; }
        .cust-card .cc-name a { color:#eee; text-decoration:none; }
        .cust-card .cc-name a:hover { color:#ff7918; }
        .cust-card .cc-meta { color:#999; font-size:12.5px; margin-top:3px; }
        .cust-card .cc-stats { display:flex; gap:16px; flex-wrap:wrap; margin-top:10px; }
        .cust-card .cc-stats div span { display:block; font-size:10px; color:#888; text-transform:uppercase; letter-spacing:.03em; }
        .cust-card .cc-stats div b { font-family: ui-monospace, monospace; font-size:15px; }
        .cust-card .cc-hot { margin-top:8px; font-size:12px; color:#f0d98a; font-weight:600; }
    </style>
</head>
<body>
    @include('partials.navbar')
    <div class="page-inner">
    <h1>Buscar reserva</h1>
    <p class="sub">Por código, nombre o teléfono del cliente.</p>

    <form method="GET" action="{{ route('reservations.search') }}">
        <input type="text" name="q" value="{{ $query }}" placeholder="HH-20260818-00001, Juan, +56912345678..." autofocus>
        <button type="submit">Buscar</button>
    </form>

    @if ($query !== '')
        @if ($customerCard)
            @php $s = $customerCard['segment']; $hot = $customerCard['monthly_visits'] >= 3; @endphp
            <div class="cust-card {{ $hot ? 'hot' : '' }}">
                <div class="cc-name">
                    <a href="{{ route('admin.customers.show', $customerCard['model']) }}" target="_blank" rel="noopener">{{ $customerCard['model']->name }}</a>
                    <span class="seg-pill seg-{{ $s['type'] }}">{{ $s['label'] }}</span>
                </div>
                <div class="cc-meta">{{ $customerCard['model']->phone_e164 }}{{ $customerCard['model']->email ? ' · '.$customerCard['model']->email : '' }}</div>
                <div class="cc-stats">
                    <div><span>Estadías previas</span><b>{{ $s['total_stays'] }}</b></div>
                    <div><span>Última visita</span><b>{{ $s['last_visit_at']?->timezone('America/Santiago')->format('d/m/Y') ?? '—' }}</b></div>
                    <div><span>Reservas este mes</span><b>{{ $customerCard['monthly_visits'] }}</b></div>
                    @if ($s['avg_interval_days'])
                        <div><span>Viene cada</span><b>{{ $s['avg_interval_days'] }}d</b></div>
                    @endif
                </div>
                @if ($hot)
                    <div class="cc-hot">⭐ Recurrente del mes — {{ $customerCard['monthly_visits'] }} reservas en {{ ucfirst(now()->locale('es')->isoFormat('MMMM')) }}</div>
                @endif
            </div>
        @endif

        @forelse ($results as $booking)
            <a class="card" href="{{ route('reservations.show', $booking->code) }}">
                <div class="row1">
                    <span class="code">{{ $booking->code }}</span>
                    <span class="pill">{{ $booking->booking_status }}</span>
                </div>
                <div class="meta">{{ $booking->room->name }} ({{ $booking->room->category->name }}) — {{ $booking->customer->name }} · {{ $booking->customer->phone_e164 }}</div>
                <div class="meta">{{ $booking->starts_at->timezone('America/Santiago')->format('d/m/Y H:i') }}</div>
            </a>
        @empty
            <p class="empty">Sin resultados para "{{ $query }}".</p>
        @endforelse
    @endif
    </div>
</body>
</html>
