<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $room->name }} — HH Motel</title>
    <style>
        :root {
            color-scheme: light;
            --hh-accent: #ff7918;
            --hh-accent-hover: #ee6909;
            --hh-canvas: #f5f5f4;
            --hh-surface: #353638;
            --hh-ink: #202124;
        }
        * { box-sizing: border-box; }
        body { background:var(--hh-canvas); color:var(--hh-ink); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; line-height: 1.45; }
        .wrap { max-width: 460px; margin: 0 auto; padding: 32px 18px 50px; }
        .brand { font-size: 13px; font-weight: 800; letter-spacing: .1em; color: var(--hh-accent); text-align:center; margin-bottom: 16px; }
        .card { background:var(--hh-surface); color:#f5f5f5; border-radius:14px; padding:22px; box-shadow: 0 3px 10px #1011120b; }
        h1 { font-size: 22px; margin: 0 0 4px; text-align:center; }
        p.cat { text-align:center; color:#9edaff; font-size:13px; margin:0 0 20px; }
        .status-badge { display:block; text-align:center; padding:10px; border-radius:9px; font-weight:800; font-size:13.5px; letter-spacing:.02em; margin-bottom:18px; }
        .status-badge.activa { background:#1c3a2a; color:#6fd39a; }
        .status-badge.aseo { background:#2a2010; color:#e8a23f; }
        .status-badge.mantencion, .status-badge.inactiva { background:#3a1c1c; color:#f3b8b8; }
        .banner { border-radius:9px; padding:12px 14px; margin-bottom:18px; font-size:13.5px; }
        .banner.ok { background:#1c3a2a; color:#6fd39a; }
        .banner.error { background:#3a1c1c; color:#f3b8b8; }
        label { display:block; font-size:12.5px; color:#c1c3c7; margin:0 0 8px; }
        select { width:100%; background:#202123; border:1px solid #62656b; color:#f4f5f7; padding:13px 12px; border-radius:8px; font-size:16px; min-height:48px; }
        button.submit { width:100%; margin-top:16px; background:var(--hh-accent); color:#21170e; border:none; padding:15px; border-radius:9px; font-size:15.5px; font-weight:800; cursor:pointer; }
        button.submit:hover { background:var(--hh-accent-hover); }
        .info { color:#c1c3c7; font-size:13px; text-align:center; margin-top:6px; }
        .staff-links { text-align:center; margin-top:18px; }
        .staff-links a { color:#62656c; text-decoration:none; font-size:13px; display:block; margin-top:8px; }
        .reported-box { background:#1c2f1c; border:1px solid #2e5a2e; color:#8fe0ad; border-radius:9px; padding:14px; text-align:center; font-size:14px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">HH MOTEL</div>
        <div class="card">
            <h1>{{ $room->name }}</h1>
            <p class="cat">Categoría {{ $room->category->name }}</p>

            <span class="status-badge {{ $room->operational_status }}">
                {{ match($room->operational_status) {
                    'activa' => '● Activa',
                    'aseo' => '◐ En aseo',
                    'mantencion' => '✕ En mantención',
                    'inactiva' => '✕ Inactiva',
                    default => strtoupper($room->operational_status),
                } }}
            </span>

            @if (session('status'))
                <div class="banner ok">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div class="banner error">{{ $error }}</div>
                @endforeach
            @endif

            @if ($room->operational_status === 'aseo')
                @if ($room->aseo_reported_at)
                    <div class="reported-box">
                        ✓ Reportada por <strong>{{ $room->aseo_reported_by }}</strong> a las {{ $room->aseo_reported_at->timezone('America/Santiago')->format('H:i') }}.<br>
                        Recepción todavía tiene que confirmarla para volver a quedar disponible.
                    </div>
                @elseif ($cleaningStaff->isEmpty())
                    <div class="banner error">No hay mucamas activas cargadas en el sistema -- avisale a recepción antes de poder reportar.</div>
                @else
                    <form method="POST" action="{{ route('rooms.qr.report_aseo', $room) }}">
                        @csrf
                        <label for="cleaned_by">¿Quién hizo el aseo?</label>
                        <select id="cleaned_by" name="cleaned_by" required>
                            <option value="" selected disabled>Elegí tu nombre</option>
                            @foreach ($cleaningStaff as $name)
                                <option value="{{ $name }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <button class="submit" type="submit">Reportar aseo listo →</button>
                    </form>
                    <p class="info">Recepción confirma después de este aviso -- la habitación no vuelve a disponible sola.</p>
                @endif
            @else
                <p class="info">Esta habitación no está esperando aseo ahora mismo.</p>
            @endif

            @auth
                <div class="staff-links">
                    <a href="{{ route('rooms.board') }}">← Ver tablero interno</a>
                    <a href="{{ route('rooms.inspections.create', $room) }}">Reportar un desperfecto →</a>
                </div>
            @endauth
        </div>
    </div>
</body>
</html>
