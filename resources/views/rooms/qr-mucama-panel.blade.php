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
        .section-hint { color:#8a8d93; font-size:11.5px; margin:-6px 0 10px; }
        .priority-tag { font-size:12px; font-weight:700; text-align:right; white-space:nowrap; }
        .pending-row.none .priority-tag { color:#8a8d93; font-weight:500; }
        .pending-row.soon { border-left-color:#e8a23f; }
        .pending-row.soon .priority-tag { color:#e8a23f; }
        .pending-row.urgent { border-left-color:#e05252; background:#3a2020; }
        .pending-row.urgent .priority-tag { color:#ff8a8a; }
        .reported h2 { color:#0e9f6e; }
        .reported .row { border-left:4px solid #34d399; }
        .occupied h2 { color:#b64c0a; }
        .occupied .row { border-left:4px solid #e88a9a; }
        .upcoming h2 { color:#7fbcdc; }
        .upcoming .row { border-left:4px solid #5b9dd9; }
        .empty { color:#8a8d93; font-size:13px; padding:6px 2px; }
        .section-hint { color:#8a8d93; font-size:11.5px; margin:-6px 0 10px; }
        .ptag { display:inline-block; font-size:10px; font-weight:700; padding:2px 8px; border-radius:20px; white-space:nowrap; margin-top:3px; }
        .ptag.sent { background:#123a28; color:#6ee7b7; }
        .ptag.unsent { background:#3a1c10; color:#ffb078; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">HH MOTEL</div>
        <h1>Panel de mucamas</h1>
        <p class="sub">
            Hola, {{ $mucama->name }} · se actualiza solo cada 60s
            <form method="POST" action="{{ route('mucamas.logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:none; border:none; color:#ff7918; font-size:inherit; cursor:pointer; text-decoration:underline; padding:0; font-family:inherit;">cerrar sesión</button>
            </form>
        </p>

        <section class="pending">
            <h2>⚠ Pendientes de aseo <span class="count">{{ $pendingAseo->count() }}</span></h2>
            <p class="section-hint">Ordenadas por prioridad -- primero las que tienen una reserva más próxima encima.</p>
            @forelse ($pendingAseo as $entry)
                @php
                    $next = $entry['status']['next_booking'];
                    $mins = $entry['status']['minutes_until_next'];
                @endphp
                <div class="row pending-row {{ $next ? ($mins <= 60 ? 'urgent' : ($mins <= 180 ? 'soon' : '')) : 'none' }}">
                    <span class="name">{{ $entry['room']->name }}<br><span class="meta">{{ $entry['room']->category->name }}</span></span>
                    <span class="priority-tag">
                        @if (!$next)
                            Sin reserva próxima
                        @elseif ($mins <= 0)
                            ⚠ Ya debería estar lista
                        @elseif ($mins <= 60)
                            ⚠ Entra en {{ $mins }} min
                        @else
                            Entra a las {{ $next->starts_at->timezone('America/Santiago')->format('H:i') }}
                        @endif
                    </span>
                </div>
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
            <p class="section-hint">Tocá una para confirmar que quedó en condiciones antes de que llegue el huésped.</p>
            @forelse ($upcoming as $entry)
                @php $checked = $entry['status']['next_booking']->room_checked_at; @endphp
                <a class="row" href="{{ route('rooms.qr.show', $entry['room']) }}" style="text-decoration:none;">
                    <span class="name">{{ $entry['room']->name }}</span>
                    <span style="text-align:right;">
                        <span class="meta" style="display:block;">{{ $entry['status']['next_booking']->starts_at->timezone('America/Santiago')->format('H:i') }}</span>
                        <span class="ptag {{ $checked ? 'sent' : 'unsent' }}">{{ $checked ? '✓ Confirmada' : 'Falta confirmar' }}</span>
                    </span>
                </a>
            @empty
                <p class="empty">Nada agendado por ahora.</p>
            @endforelse
        </section>
    </div>
</body>
</html>
