<!doctype html>
<html lang="es" style="background:#111;color:#eee">
<head>
    <meta charset="utf-8"><meta name="color-scheme" content="dark">
    <meta http-equiv="refresh" content="30">
    <title>Tablero de Playrooms — HH Motel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; }
        .page-inner { max-width: 1700px; width:100%; margin: 0; padding: 24px 24px 60px; }
        .flash-status { background:#14251c; border:1px solid #2e6e45; color:#8fe0ad; padding:12px 16px; border-radius:9px; font-size:14px; font-weight:600; margin-bottom:20px; }
        .topline { margin-bottom: 26px; display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; }
        .topline-toolbar { display:flex; gap:8px; flex-wrap:wrap; flex:none; }
        a.compact-toggle { text-decoration:none; display:inline-flex; align-items:center; }
        h1 { font-size: 18px; letter-spacing: .04em; margin-bottom: 2px; }
        .sub { color:#999; font-size: 13px; margin:0; }
        .sub a { color:#ff7918; text-decoration:none; }

        .summary { display:flex; gap:10px; flex-wrap:wrap; margin-bottom: 30px; }
        .summary .chip { display:flex; align-items:baseline; gap:7px; background:#1c1c1c; border:1px solid #333; border-radius:8px; padding:9px 14px; }
        .summary .chip b { font-size:18px; font-family: ui-monospace, monospace; }
        .summary .chip span { font-size:11.5px; color:#999; text-transform:uppercase; letter-spacing:.04em; }
        .summary .chip.ocupadas b { color:#e88a9a; }
        .summary .chip.aseo b { color:#e8c76f; }
        .summary .chip.disponibles b { color:#6fd39a; }
        .summary .chip.proximas b { color:#e8c76f; }
        .summary .chip.fuera b { color:#aaa; }

        a.sales-btn { background:#14251c; border-color:#2e6e45; color:#8fe0ad; font-weight:600; }
        a.sales-btn:hover { border-color:#6fd39a; color:#fff; }

        .wing-toggle-row { display:flex; align-items:center; gap:12px; margin-bottom:16px; flex-wrap:wrap; }
        .wing-toggle { display:inline-flex; align-items:center; gap:9px; background:#1c1c1c; border:1px solid #333; color:#ccc; border-radius:20px; padding:8px 16px 8px 12px; font-size:12.5px; font-weight:600; cursor:pointer; }
        .wing-toggle-dot { width:9px; height:9px; border-radius:50%; background:#555; flex:none; }
        .wing-toggle.on { border-color:#2e6e45; background:#14251c; color:#8fe0ad; }
        .wing-toggle.on .wing-toggle-dot { background:#6fd39a; }
        .wing-toggle.off { border-color:#7a2d2d; background:#2a1c1c; color:#e8a2a2; }
        .wing-toggle.off .wing-toggle-dot { background:#e05252; }
        .wing-toggle:hover { border-color:#ff7918; }
        .wing-hidden-note { font-size:12px; color:#888; }

        .daily-summary { display:grid; grid-template-columns:repeat(5, 1fr); gap:8px; margin-bottom:30px; }
        @media (max-width: 760px) { .daily-summary { grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); } }
        .dsum-tile { display:flex; flex-direction:column; gap:3px; background:#14251c; border:1px solid #2e6e45; border-radius:9px; padding:10px 12px; text-decoration:none; min-width:0; }
        .dsum-tile:hover { border-color:#6fd39a; }
        .dsum-tile.total { background:#1c1c1c; border-color:#ff7918; }
        .dsum-label { font-size:10px; color:#9cc7ac; text-transform:uppercase; letter-spacing:.02em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .dsum-tile.total .dsum-label { color:#ffb379; }
        .dsum-value { font-family: ui-monospace, monospace; font-weight:700; font-size:16px; color:#eee; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        .page-grid { display:grid; grid-template-columns: 1fr 280px; gap:28px; align-items:start; }
        @media (max-width: 1100px) { .page-grid { grid-template-columns: 1fr; } }

        .section { margin-bottom: 34px; }
        .section-head { display:flex; align-items:baseline; gap:10px; margin-bottom: 14px; }
        .section-head h2 { font-size: 14px; margin:0; text-transform:uppercase; letter-spacing:.05em; }
        .section-head .count { font-family: ui-monospace, monospace; font-size: 12px; color:#777; }
        .section-head .bar { flex:1; height:1px; background:#292929; }
        .section.ocupadas .section-head h2 { color:#e88a9a; }
        .section.aseo .section-head h2 { color:#e8c76f; }
        .section.disponibles .section-head h2 { color:#6fd39a; }
        .section.fuera .section-head h2 { color:#aaa; }
        .section-empty { color:#666; font-size:13px; }

        .cat-filter { display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px; }
        .cat-filter button { background:#1c1c1c; border:1px solid #333; color:#888; border-radius:7px; padding:6px 13px; font-size:12px; font-weight:600; cursor:pointer; }
        /* Cada chip lleva el color de su categoría (mismo que el borde de la tarjeta). */
        .cat-filter button[data-cat="todas"] { color:#6fd39a; }
        .cat-filter button[data-cat="go"]   { color:#5b9dd9; }
        .cat-filter button[data-cat="lite"] { color:#4ecdc4; }
        .cat-filter button[data-cat="plus"] { color:#b088e8; }
        .cat-filter button[data-cat="max"]  { color:#f2994a; }
        .cat-filter button[data-cat="new-lite"] { color:#e8c76f; background:#2a2410; border-color:#6b5a1e; }
        .cat-filter button:hover { border-color: currentColor; }
        .cat-filter button.active { border-color: currentColor; background:#242424; }
        .cat-filter button[data-cat="new-lite"].active { background:#3a3216; }
        .cat-hidden { display:none !important; }

        .sidebar { position:sticky; top:20px; }
        .sidebar-title { display:flex; align-items:baseline; gap:8px; margin-bottom:12px; }
        .sidebar-title h2 { font-size: 13px; margin:0; text-transform:uppercase; letter-spacing:.05em; color:#e8c76f; }
        .sidebar-title .count { font-family: ui-monospace, monospace; font-size: 12px; color:#777; }
        .sidebar-hint { font-size:11.5px; color:#999; margin:0 0 14px; line-height:1.4; }
        .sidebar-empty { color:#666; font-size:12.5px; border:1px dashed #333; border-radius:8px; padding:14px; text-align:center; }
        .proxima-card { background:#231f14; border:1px solid #5a5230; border-left-width:4px; border-radius:10px; padding:10px 12px; margin-bottom:10px; max-width:520px; transition: transform .13s ease, box-shadow .13s ease; }
        .proxima-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(232,199,111,.18); }
        .proxima-card.cat-go { border-left-color:#5b9dd9; }
        .proxima-card.cat-lite { border-left-color:#4ecdc4; }
        .proxima-card.cat-plus { border-left-color:#b088e8; }
        .proxima-card.cat-max { border-left-color:#f2994a; }
        .proxima-card.cat-new-lite { border-left-color:#e8c76f; }
        .proxima-card .card-head { display:flex; justify-content:space-between; align-items:baseline; margin-bottom:2px; }
        .proxima-card .card-head b { font-size:14px; }
        .proxima-card .cat { font-size:11px; color:#999; margin-bottom:8px; }
        .proxima-card .guest { font-size:12px; color:#ddd; margin-bottom:2px; }
        .proxima-card .when { font-size:11px; color:#e8c76f; font-weight:600; margin-bottom:8px; }
        .proxima-card form { margin-top:0; }
        .checkin-btn { display:block; width:100%; text-align:center; background:#ff7918; color:#fff; border:1px solid #ff7918; text-decoration:none; padding:8px; border-radius:7px; font-size:12.5px; font-weight:600; cursor:pointer; }
        .checkin-btn:hover { background:#df6209; }

        .grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 16px; }
        .card { background:#1c1c1c; border:1px solid #333; border-left-width:4px; border-radius: 10px; padding: 14px 16px; transition: transform .13s ease, box-shadow .13s ease, border-color .13s ease; }
        /* Solo transform + box-shadow: los compone la GPU, no re-maquetan la grilla — no se siente lento. */
        .card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.45); border-color:#555; }
        .card.cat-go:hover { border-left-color:#7fbfe8; box-shadow: 0 6px 20px rgba(91,157,217,.22); }
        .card.cat-lite:hover { border-left-color:#6fe4db; box-shadow: 0 6px 20px rgba(78,205,196,.22); }
        .card.cat-plus:hover { border-left-color:#c9a6f5; box-shadow: 0 6px 20px rgba(176,136,232,.22); }
        .card.cat-max:hover { border-left-color:#f7b06a; box-shadow: 0 6px 20px rgba(242,153,74,.22); }
        .card.cat-new-lite { border-left-width:3px; box-shadow: 0 0 0 1px rgba(232,199,111,.25) inset; }
        .card.cat-new-lite:hover { border-left-color:#f0d98a; box-shadow: 0 6px 20px rgba(232,199,111,.3); }
        @media (prefers-reduced-motion: reduce) { .card { transition: none; } .card:hover { transform: none; } }
        .card-head { display:flex; justify-content:space-between; align-items:baseline; gap:6px; margin-bottom:2px; }
        .card-head b { font-size: 16px; }
        .offer-badge { font-size:11px; font-weight:800; background:linear-gradient(135deg,#ff8a1f,#e94d13); color:#fff; border:1px solid #ffb15c; border-radius:7px; padding:5px 9px; white-space:nowrap; flex:none; box-shadow:0 3px 10px rgba(255,112,20,.28); letter-spacing:.01em; }
        .compact .offer-badge { font-size:10px; padding:4px 7px; }
        .maintenance-badge { font-size:10.5px; font-weight:700; background:#3a1c22; color:#e88a9a; border:1px solid #7a2d2d; border-radius:20px; padding:2px 8px; white-space:nowrap; flex:none; }
        .compact .maintenance-badge { font-size:9px; padding:1px 6px; }
        .card.needs-maintenance { box-shadow: 0 0 0 1px rgba(232,138,154,.4) inset; }
        a.inspect-btn { display:block; text-align:center; margin-top:8px; text-decoration:none; padding:7px; border-radius:7px; font-size:12px; font-weight:600; background:transparent; border:1px dashed #4a3a3a; color:#c98a8a; }
        a.inspect-btn:hover { border-color:#e88a9a; color:#e88a9a; border-style:solid; }
        .compact a.inspect-btn { padding: 5px; font-size: 10.5px; margin-top: 5px; }
        .cat { font-size: 11.5px; color:#999; margin-bottom: 10px; }
        .room-photo-link { display:flex; align-items:center; gap:6px; margin:-4px 0 10px; text-decoration:none; }
        .room-photo-link img { width:32px; height:24px; object-fit:cover; border-radius:4px; border:1px solid #333; }
        .room-photo-link span { font-size:10.5px; color:#888; }
        .room-photo-link:hover span { color:#ff7918; }
        .compact .room-photo-link { margin-top:-2px; margin-bottom:5px; }
        .compact .room-photo-link img { width:24px; height:18px; }
        .chips .room-photo-link { display:none; }
        /* Franja de color por categoría — para reconocer de un vistazo sin leer el texto. */
        .card.cat-go { border-left-color:#5b9dd9; }
        .card.cat-lite { border-left-color:#4ecdc4; }
        .card.cat-plus { border-left-color:#b088e8; }
        .card.cat-max { border-left-color:#f2994a; }
        .card.cat-new-lite { border-left-color:#e8c76f; }
        .cat-go .cat { color:#5b9dd9; }
        .cat-lite .cat { color:#4ecdc4; }
        .cat-plus .cat { color:#b088e8; }
        .cat-max .cat { color:#f2994a; }
        .cat-new-lite .cat { color:#e8c76f; font-weight:700; }
        .pill { display:inline-block; font-family: ui-monospace, monospace; font-size: 11px; padding:3px 9px; border-radius:20px; font-weight:600; margin-bottom:6px; }
        .pill-libre { background:#1c3a2a; color:#6fd39a; }
        .pill-ocupada { background:#3a1c22; color:#e88a9a; }
        .pill-reservada { background:#2a2010; color:#e8a23f; }
        .pill-aseo { background:#3a331c; color:#e8c76f; }
        .pill-upcoming { background:#2a2010; color:#e8a23f; margin-left:6px; }
        .pill-mantencion, .pill-inactiva { background:#2a2a2a; color:#aaa; }
        .pill-activa { background:#1c2f3a; color:#7fbcdc; }
        .eta { font-size: 11.5px; color:#999; margin-top: 6px; }
        .eta.overtime { color:#e88a9a; font-weight:600; }
        .overdue-room { border-color:#e05252 !important; box-shadow:0 0 0 2px rgba(224,82,82,.2), 0 8px 22px rgba(224,82,82,.12); }
        .overdue-room .overtime { color:#ff7777; font-weight:800; animation: overdue-warning 1.5s ease-in-out infinite; }
        .overdue-room .pill-ocupada { background:#7d2635; color:#fff; }
        @keyframes overdue-warning { 0%,100% { opacity:1; text-shadow:0 0 0 rgba(255,82,82,0); } 50% { opacity:.45; text-shadow:0 0 10px rgba(255,82,82,.9); } }
        @media (prefers-reduced-motion: reduce) { .overdue-room .overtime { animation:none; } }
        .code { font-family: ui-monospace, monospace; font-size: 11px; margin-top:2px; color:#888; }
        a.consumo-btn { display:block; text-align:center; margin-top:6px; background:#1c2f3a; border:1px solid #3a5a72; color:#7fbcdc; text-decoration:none; padding:8px; border-radius:7px; font-size:12.5px; font-weight:600; }
        a.consumo-btn:hover { border-color:#7fbcdc; }
        details { margin-top: 8px; }
        summary {
            font-size: 12px; color:#ccc; cursor:pointer; list-style:none;
            text-align:center; padding:8px; border-radius:7px; border:1px solid #3a3a3a; background:#242424;
        }
        summary::-webkit-details-marker { display:none; }
        summary:hover { border-color:#666; color:#fff; }
        details[open] summary { border-color:#ff7918; color:#ff7918; margin-bottom:8px; }
        form { margin-top: 8px; display:flex; gap:6px; }
        select { flex:1; background:#111; border:1px solid #333; color:#eee; border-radius:6px; font-size:12px; padding:6px; }
        button { background:#2a2a2a; color:#eee; border:1px solid #444; border-radius:6px; font-size:11.5px; padding:4px 10px; cursor:pointer; }
        button:hover { border-color:#ff7918; }
        a.reserve-btn { display:block; text-align:center; margin-top:10px; background:#ff7918; border:1px solid #ff7918; color:#fff; text-decoration:none; padding:8px; border-radius:7px; font-size:12.5px; font-weight:600; }
        .aseo-ready-form { margin-top:8px; display:block; }
        .aseo-cleaner-input { display:block; width:100%; box-sizing:border-box; margin-bottom:6px; }
        .aseo-cleaner-input:focus { outline:none; border-color:#e8c76f; }
        .aseo-ready-btn { display:block; width:100%; text-align:center; background:#3a331c; border:1px solid #6b5a1e; color:#e8c76f; padding:8px; border-radius:7px; font-size:12.5px; font-weight:600; cursor:pointer; }
        .aseo-ready-btn:hover { border-color:#e8c76f; }
        a.checkout-btn { display:block; text-align:center; margin-top:8px; background:#2a2a2a; border:1px solid #444; color:#eee; text-decoration:none; padding:8px; border-radius:7px; font-size:12.5px; font-weight:600; }
        a.checkout-btn:hover { border-color:#ff7918; color:#ff7918; }
        a.checkout-btn.pending-balance { background:#2a2010; border-color:#6b5a1e; color:#e8a23f; }
        a.checkout-btn.pending-balance:hover { border-color:#e8a23f; color:#ffcf7a; }
        a.bookings-btn { display:block; text-align:center; margin-top:8px; text-decoration:none; padding:8px; border-radius:7px; font-size:12.5px; font-weight:600; }
        a.bookings-btn.has-bookings { background:#1c2f3a; border:1px solid #3a5a72; color:#7fbcdc; }
        a.bookings-btn.has-bookings:hover { border-color:#7fbcdc; }
        a.bookings-btn.empty { background:transparent; border:1px dashed #333; color:#666; font-weight:500; }
        a.bookings-btn.empty:hover { border-color:#555; color:#999; }

        /* Vista compacta — para cuando hay muchas habitaciones y hay que ver más de un vistazo. */
        .compact-toggle { background:#1c1c1c; border:1px solid #333; color:#ccc; border-radius:7px; padding:8px 13px; font-size:12.5px; font-weight:600; cursor:pointer; white-space:nowrap; }
        .compact-toggle:hover { border-color:#ff7918; color:#ff7918; }
        .compact .grid { grid-template-columns: repeat(auto-fill, minmax(148px, 1fr)); gap: 10px; }
        .compact .card { padding: 8px 10px; }
        .compact .card-head b { font-size: 13px; }
        .compact .cat { font-size: 10px; margin-bottom: 4px; }
        .compact .pill { font-size: 9.5px; padding: 2px 7px; margin-bottom: 3px; }
        .compact .eta { font-size: 10px; margin-top: 3px; }
        .compact .code { font-size: 9px; }
        .compact a.reserve-btn, .compact .aseo-ready-btn, .compact a.bookings-btn, .compact a.checkout-btn, .compact .checkin-btn, .compact a.consumo-btn { padding: 5px; font-size: 10.5px; margin-top: 5px; }
        .compact .aseo-cleaner-input { padding: 5px 7px; font-size: 10.5px; margin-bottom: 5px; }
        .compact details { margin-top: 5px; }
        .compact summary { padding: 5px; font-size: 10.5px; }
        .compact details form { flex-direction: column; gap: 5px; }
        .compact details select { width: 100%; font-size: 10.5px; padding: 5px; }
        .compact details button { width: 100%; font-size: 10.5px; padding: 5px; }
        .compact .section { margin-bottom: 20px; }
        .compact .proxima-card { padding: 8px 10px; margin-bottom: 8px; }

        /* Vista chips — máxima densidad: solo categoría, número y estado.
           El resto (botones, código, detalle de estado) se oculta; la
           tarjeta entera pasa a ser clickeable hacia el detalle de la
           habitación (ver hhBindChipClicks). */
        .chips .grid { grid-template-columns: repeat(auto-fill, minmax(96px, 1fr)); gap: 8px; }
        .chips .section { margin-bottom: 18px; }
        .chips .card { padding: 9px 10px; cursor: pointer; }
        .chips .card:hover { border-color: #666; }
        .chips .card-head b { font-size: 12.5px; }
        .chips .cat { font-size: 9.5px; margin-bottom: 5px; }
        .chips .pill { font-size: 9px; padding: 2px 6px; margin-bottom: 0; }
        .chips .eta, .chips .code, .chips .offer-badge, .chips .maintenance-badge,
        .chips a.reserve-btn, .chips .aseo-ready-btn, .chips a.bookings-btn,
        .chips a.checkout-btn, .chips .checkin-btn, .chips a.consumo-btn, .chips a.inspect-btn,
        .chips details, .chips form { display: none !important; }
        .chips .proxima-card { padding: 8px 10px; margin-bottom: 8px; }
    </style>
    <script>
        // Tres niveles de densidad: normal → compacta → chips → normal.
        // Se aplica antes de pintar para no hacer parpadear el layout.
        // 'hh-compact-board'='1' es el valor viejo (antes de que existiera
        // "chips") — se migra a 'compact' para no perder la preferencia.
        (function () {
            let density = localStorage.getItem('hh-board-density');
            if (!density) density = localStorage.getItem('hh-compact-board') === '1' ? 'compact' : 'normal';
            if (density === 'compact' || density === 'chips') {
                document.documentElement.classList.add(density);
            }
        })();
    </script>
</head>
<body>
    @include('partials.navbar')
    <div class="page-inner">
    @if (session('status'))
        <div class="flash-status">{{ session('status') }}</div>
    @endif
    <div class="topline">
        <div>
            <h1>HH MOTEL — Tablero de Playrooms</h1>
            <p class="sub">Se actualiza solo cada 30s · <a href="{{ route('rooms.board') }}">actualizar ahora</a></p>
        </div>
        <div class="topline-toolbar">
            <a class="compact-toggle sales-btn" href="{{ route('sales.daily') }}">Ventas del día →</a>
            <a class="compact-toggle" href="{{ route('calendar.index') }}">Calendario</a>
            <button type="button" class="compact-toggle" id="catalog-link-btn" onclick="navigator.clipboard.writeText('{{ route('catalog.index') }}'); this.textContent='¡Copiado! — pegalo en WhatsApp'; setTimeout(() => this.textContent='Copiar link del catálogo', 2000);">Copiar link del catálogo</button>
            <button type="button" class="compact-toggle" id="compact-toggle-btn" onclick="hhToggleCompact()">⊟ Vista compacta</button>
        </div>
    </div>

    <div class="wing-toggle-row">
        <form method="POST" action="{{ route('rooms.ala_sur.toggle') }}">
            @csrf
            <button type="submit" class="wing-toggle {{ $alaSurEnabled ? 'on' : 'off' }}">
                <span class="wing-toggle-dot"></span>
                Ala Sur {{ $alaSurEnabled ? 'habilitada' : 'deshabilitada' }}
            </button>
        </form>
        @if (! $alaSurEnabled && $hiddenSurCount > 0)
            <span class="wing-hidden-note">{{ $hiddenSurCount }} {{ Str::plural('habitación', $hiddenSurCount) }} libre{{ $hiddenSurCount === 1 ? '' : 's' }} oculta{{ $hiddenSurCount === 1 ? '' : 's' }} de Disponibles</span>
        @endif
    </div>

    <div class="summary">
        <div class="chip ocupadas"><b>{{ $grouped['ocupadas']->count() }}</b><span>Ocupadas</span></div>
        <div class="chip aseo"><b>{{ $grouped['en_aseo']->count() }}</b><span>En aseo</span></div>
        <div class="chip disponibles"><b>{{ $grouped['disponibles']->count() }}</b><span>Disponibles</span></div>
        <div class="chip proximas"><b>{{ $grouped['proximas']->count() }}</b><span>Por llegar</span></div>
        <div class="chip fuera"><b>{{ $grouped['fuera_de_servicio']->count() }}</b><span>Fuera de servicio</span></div>
    </div>

    <div class="daily-summary">
        <a href="{{ route('sales.daily') }}" class="dsum-tile">
            <span class="dsum-label">Playrooms vendidos hoy</span>
            <span class="dsum-value">{{ $dailySummary['rooms_count'] }}</span>
        </a>
        <a href="{{ route('sales.daily') }}" class="dsum-tile">
            <span class="dsum-label">Extras vendidos hoy</span>
            <span class="dsum-value">{{ $dailySummary['extras_count'] }}</span>
        </a>
        <a href="{{ route('sales.daily') }}" class="dsum-tile">
            <span class="dsum-label">Venta Playrooms</span>
            <span class="dsum-value">${{ number_format($dailySummary['rooms_revenue'], 0, ',', '.') }}</span>
        </a>
        <a href="{{ route('sales.daily') }}" class="dsum-tile">
            <span class="dsum-label">Venta extras</span>
            <span class="dsum-value">${{ number_format($dailySummary['extras_revenue'], 0, ',', '.') }}</span>
        </a>
        <a href="{{ route('sales.daily') }}" class="dsum-tile total">
            <span class="dsum-label">Total del día</span>
            <span class="dsum-value">${{ number_format($dailySummary['rooms_revenue'] + $dailySummary['extras_revenue'], 0, ',', '.') }}</span>
        </a>
    </div>

    <div class="page-grid">
        <div class="main-col">
            <div class="section ocupadas">
                <div class="section-head"><h2>Ocupadas</h2><span class="count">{{ $grouped['ocupadas']->count() }}</span><span class="bar"></span></div>
                @if ($grouped['ocupadas']->isEmpty())
                    <p class="section-empty">Ninguna habitación ocupada en este momento.</p>
                @else
                    <div class="grid">
                        @foreach ($grouped['ocupadas'] as $entry)
                            @include('rooms._card', ['entry' => $entry])
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="section aseo">
                <div class="section-head"><h2>En aseo</h2><span class="count">{{ $grouped['en_aseo']->count() }}</span><span class="bar"></span></div>
                @if ($grouped['en_aseo']->isEmpty())
                    <p class="section-empty">Ninguna habitación esperando aseo.</p>
                @else
                    <p class="section-empty" style="margin-bottom:12px;">No vuelven a disponibles hasta que recepción marque "aseo listo" en cada una.</p>
                    <div class="grid">
                        @foreach ($grouped['en_aseo'] as $entry)
                            @include('rooms._card', ['entry' => $entry])
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="section disponibles">
                <div class="section-head"><h2>Disponibles</h2><span class="count" id="disponibles-count">{{ $grouped['disponibles']->count() }}</span><span class="bar"></span></div>
                @php
                    $boardCategories = collect()
                        ->concat($grouped['ocupadas'])->concat($grouped['en_aseo'])->concat($grouped['disponibles'])
                        ->concat($grouped['proximas'])->concat($grouped['fuera_de_servicio'])
                        ->pluck('room.category')->unique('id')->sortBy('display_order')->values();
                @endphp
                @if ($boardCategories->count() > 1)
                    <div class="cat-filter" id="cat-filter">
                        <button type="button" data-cat="todas" onclick="hhFilterCategory('todas')">Todas</button>
                        @foreach ($boardCategories as $cat)
                            <button type="button" data-cat="{{ Str::slug($cat->name) }}" onclick="hhFilterCategory('{{ Str::slug($cat->name) }}')">{{ $cat->name }}</button>
                        @endforeach
                    </div>
                @endif
                @if ($grouped['disponibles']->isEmpty())
                    <p class="section-empty">No hay habitaciones disponibles ahora mismo.</p>
                @else
                    <div class="grid" id="disponibles-grid" data-total="{{ $grouped['disponibles']->count() }}">
                        @foreach ($grouped['disponibles'] as $entry)
                            @include('rooms._card', ['entry' => $entry])
                        @endforeach
                    </div>
                    <p class="section-empty cat-hidden" id="disponibles-none">Ninguna habitación disponible en esa categoría ahora mismo.</p>
                @endif
            </div>

            <div class="section fuera">
                <div class="section-head"><h2>Fuera de servicio</h2><span class="count">{{ $grouped['fuera_de_servicio']->count() }}</span><span class="bar"></span></div>
                @if ($grouped['fuera_de_servicio']->isEmpty())
                    <p class="section-empty">Ninguna habitación fuera de servicio.</p>
                @else
                    <div class="grid">
                        @foreach ($grouped['fuera_de_servicio'] as $entry)
                            @include('rooms._card', ['entry' => $entry])
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="sidebar">
            <div class="sidebar-title"><h2>Por llegar</h2><span class="count">{{ $grouped['proximas']->count() }}</span></div>
            <p class="sidebar-hint">Habitaciones libres pero con una reserva tan próxima (o que ya debería haber llegado) que ya no conviene ofrecerlas a un walk-in. Marca el check-in apenas llegue el cliente.</p>
            @if ($grouped['proximas']->isEmpty())
                <p class="sidebar-empty">Ninguna por ahora.</p>
            @else
                @foreach ($grouped['proximas'] as $entry)
                    @php $mins = $entry['status']['minutes_until_next']; @endphp
                    <div class="proxima-card cat-{{ Str::slug($entry['room']->category->name) }}">
                        <div class="card-head"><b>{{ $entry['room']->name }}</b></div>
                        <div class="cat">{{ $entry['room']->category->name }}</div>
                        <div class="guest">{{ $entry['status']['next_booking']->customer->name }}</div>
                        <div class="when">
                            @if ($mins > 0) llega en {{ \App\Support\TimeFormat::minutes($mins) }}
                            @elseif ($mins === 0) llegando ahora
                            @else atrasada {{ \App\Support\TimeFormat::minutes(abs($mins)) }} — sin check-in
                            @endif
                        </div>
                        <a class="checkin-btn" href="{{ route('bookings.checkin.show', $entry['status']['next_booking']->code) }}">Marcar check-in</a>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
    </div>
    <script>
        // Ciclo de densidad: normal → compacta → chips → normal. Un solo
        // botón en vez de sumar más controles al toolbar — pedido explícito
        // de no llenar de elementos esa zona.
        const DENSITY_LABELS = { normal: '⊟ Vista compacta', compact: '▦ Vista chips', chips: '⊞ Vista normal' };
        const DENSITY_NEXT = { normal: 'compact', compact: 'chips', chips: 'normal' };
        function hhCurrentDensity() {
            if (document.documentElement.classList.contains('chips')) return 'chips';
            if (document.documentElement.classList.contains('compact')) return 'compact';
            return 'normal';
        }
        function hhSetCompactLabel() {
            document.getElementById('compact-toggle-btn').textContent = DENSITY_LABELS[hhCurrentDensity()];
        }
        function hhToggleCompact() {
            const next = DENSITY_NEXT[hhCurrentDensity()];
            document.documentElement.classList.remove('compact', 'chips');
            if (next !== 'normal') document.documentElement.classList.add(next);
            localStorage.setItem('hh-board-density', next);
            hhSetCompactLabel();
        }
        hhSetCompactLabel();

        // Vista chips: la tarjeta entera es clickeable hacia el detalle de
        // la habitación (sus botones quedan ocultos por CSS en ese modo).
        document.querySelectorAll('.grid .card[data-href]').forEach(function (card) {
            card.addEventListener('click', function (event) {
                if (event.target.closest('a, button, input, select, form')) return;
                if (hhCurrentDensity() === 'chips' || card.classList.contains('overdue-room') || card.querySelector('.pill-ocupada')) window.location.href = card.dataset.href;
            });
        });

        // Filtro de categoría — solo afecta la sección "Disponibles". Las
        // ocupadas y las "por llegar" quedan siempre visibles. La elección
        // se guarda y se re-aplica en cada refresco automático (cada 30s).
        function hhFilterCategory(cat) {
            localStorage.setItem('hh-board-category', cat);
            hhApplyCategoryFilter();
        }
        function hhApplyCategoryFilter() {
            const grid = document.getElementById('disponibles-grid');
            const filterBar = document.getElementById('cat-filter');
            if (!filterBar) return;
            let cat = localStorage.getItem('hh-board-category') || 'todas';
            if (cat !== 'todas' && !filterBar.querySelector('button[data-cat="' + cat + '"]')) {
                cat = 'todas';
            }
            filterBar.querySelectorAll('button').forEach(b => b.classList.toggle('active', b.dataset.cat === cat));

            const countEl = document.getElementById('disponibles-count');
            if (!grid) { return; }

            let shown = 0;
            grid.querySelectorAll('.card').forEach(card => {
                const match = cat === 'todas' || card.classList.contains('cat-' + cat);
                card.classList.toggle('cat-hidden', !match);
                if (match) shown++;
            });

            const total = grid.dataset.total;
            if (countEl) countEl.textContent = cat === 'todas' ? total : (shown + ' de ' + total);
            const none = document.getElementById('disponibles-none');
            if (none) none.classList.toggle('cat-hidden', shown > 0);
        }
        hhApplyCategoryFilter();
    </script>
</body>
</html>
