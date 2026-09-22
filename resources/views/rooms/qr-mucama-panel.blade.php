<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta http-equiv="refresh" content="60">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de mucamas — HH Motel</title>
    <style>
        :root {
            color-scheme: light;
            --hh-accent: #ff7918;
            --hh-canvas: #f5f5f4;
            --hh-surface: #353638;
            --hh-ink: #202124;
        }
        * { box-sizing: border-box; }
        body { background:var(--hh-canvas); color:var(--hh-ink); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; margin: 0; line-height: 1.4; }
        .wrap { max-width: 520px; margin: 0 auto; padding: 24px 16px 50px; }
        .brand { font-size: 13px; font-weight: 800; letter-spacing: .1em; color: var(--hh-accent); text-align:center; margin-bottom: 6px; }
        h1 { font-size: 20px; margin: 0 0 4px; text-align:center; }
        p.sub { text-align:center; color:#62656c; font-size:12.5px; margin:0 0 22px; }
        section { margin-bottom: 22px; }
        section h2 { font-size:12.5px; text-transform:uppercase; letter-spacing:.05em; margin:0 0 10px; display:flex; align-items:baseline; gap:8px; }
        section h2 .count { font-family: ui-monospace, monospace; color:#999; font-weight:500; text-transform:none; }
        .row { display:flex; justify-content:space-between; align-items:center; background:var(--hh-surface); color:#f5f5f5; border-radius:10px; padding:13px 15px; margin-bottom:8px; }
        .row .name { font-weight:700; font-size:14.5px; }
        .row .meta { font-size:12px; color:#c1c3c7; }
        .row .right { text-align:right; font-size:12.5px; }
        .pending h2 { color:#956009; }
        .pending .row { border-left:4px solid #e8a23f; }
        .reported h2 { color:#0e9f6e; }
        .reported .row { border-left:4px solid #34d399; }
        .occupied h2 { color:#b64c0a; }
        .occupied .row { border-left:4px solid #e88a9a; }
        .upcoming h2 { color:#7fbcdc; }
        .upcoming .row { border-left:4px solid #5b9dd9; }
        .empty { color:#8a8d93; font-size:13px; padding:6px 2px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">HH MOTEL</div>
        <h1>Panel de mucamas</h1>
        <p class="sub">Se actualiza solo cada 60s · sin datos de clientes ni de dinero</p>

        <section class="pending">
            <h2>⚠ Pendientes de aseo <span class="count">{{ $pendingAseo->count() }}</span></h2>
            @forelse ($pendingAseo as $entry)
                <div class="row"><span class="name">{{ $entry['room']->name }}</span><span class="meta">{{ $entry['room']->category->name }}</span></div>
            @empty
                <p class="empty">Nada pendiente ahora mismo.</p>
            @endforelse
        </section>

        <section class="reported">
            <h2>✓ Reportadas, falta confirmar <span class="count">{{ $reportedAseo->count() }}</span></h2>
            @forelse ($reportedAseo as $entry)
                <div class="row"><span class="name">{{ $entry['room']->name }}</span><span class="right">{{ $entry['room']->aseo_reported_by }}<br><span class="meta">{{ $entry['room']->aseo_reported_at->timezone('America/Santiago')->format('H:i') }}</span></span></div>
            @empty
                <p class="empty">Nada esperando confirmación.</p>
            @endforelse
        </section>

        <section class="occupied">
            <h2>● Ocupadas ahora <span class="count">{{ $occupied->count() }}</span></h2>
            @forelse ($occupied as $entry)
                <div class="row"><span class="name">{{ $entry['room']->name }}</span><span class="meta">{{ $entry['status']['eta_minutes'] !== null && $entry['status']['eta_minutes'] >= 0 ? 'libera en '.\App\Support\TimeFormat::minutes($entry['status']['eta_minutes']) : 'pasó su horario' }}</span></div>
            @empty
                <p class="empty">Ninguna ocupada ahora.</p>
            @endforelse
        </section>

        <section class="upcoming">
            <h2>◷ Próximas a llegar <span class="count">{{ $upcoming->count() }}</span></h2>
            @forelse ($upcoming as $entry)
                <div class="row"><span class="name">{{ $entry['room']->name }}</span><span class="meta">{{ $entry['status']['next_booking']->starts_at->timezone('America/Santiago')->format('H:i') }}</span></div>
            @empty
                <p class="empty">Nada agendado por ahora.</p>
            @endforelse
        </section>
    </div>
</body>
</html>
