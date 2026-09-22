<!doctype html>
<html lang="es" style="background:#111;color:#eee">
<head>
    <meta charset="utf-8"><meta name="color-scheme" content="dark">
    <meta http-equiv="refresh" content="30">
    <title>Dónde están las mucamas — HH Motel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; }
        .page-inner { max-width: 760px; margin: 0; padding: 24px 24px 60px; }
        h1 { font-size: 18px; letter-spacing: .04em; margin-bottom: 2px; }
        .sub { color:#999; font-size: 13px; margin:0 0 22px; }
        .mucama-row { display:flex; align-items:center; justify-content:space-between; gap:14px; background:#1c1c1c; border:1px solid #333; border-radius:10px; padding:16px 18px; margin-bottom:10px; }
        .mucama-row .who strong { display:block; font-size:15px; }
        .mucama-row .who small { color:#999; font-size:12px; }
        .mucama-row .where { text-align:right; }
        .mucama-row .where .room { font-weight:700; font-size:14px; }
        .mucama-row .where .ago { font-size:11.5px; color:#999; margin-top:2px; }
        .status-tag { display:inline-block; margin-top:5px; font-size:10.5px; font-weight:800; text-transform:uppercase; letter-spacing:.03em; padding:3px 9px; border-radius:20px; }
        .status-tag.working { background:#2a2010; color:#e8a23f; }
        .status-tag.reported { background:#0f2b22; color:#6ee7b7; }
        .status-tag.done { background:#1a2a1a; color:#7fbf8f; }
        .status-tag.none { background:#262626; color:#888; }
        .shift-tag { display:inline-block; margin-top:4px; font-size:11px; }
        .shift-tag.on { color:#6ee7b7; }
        .shift-tag.off { color:#777; }
        .empty { color:#777; padding:30px; text-align:center; }
        h2.section { font-size:14px; margin:30px 0 12px; color:#999; text-transform:uppercase; letter-spacing:.04em; }
        table.log-table { width:100%; border-collapse:collapse; font-size:13px; }
        table.log-table th { text-align:left; color:#888; font-weight:500; font-size:11px; text-transform:uppercase; padding:0 10px 8px 0; }
        table.log-table td { padding:8px 10px 8px 0; border-top:1px solid #292929; }
        table.log-table td.open { color:#6ee7b7; font-weight:700; }
    </style>
</head>
<body>
@include('partials.navbar')
<div class="page-inner">
    <h1>HH MOTEL — Dónde están las mucamas</h1>
    <p class="sub">Última habitación que cada una escaneó por QR y cuándo · se actualiza solo cada 30s · <a href="{{ route('mucamas.activity') }}" style="color:#ff7918;">actualizar ahora</a></p>

    @forelse ($mucamas as $mucama)
        <div class="mucama-row">
            <div class="who">
                <strong>{{ $mucama->name }}</strong>
                @if ($mucama->shiftLogs->isNotEmpty())
                    <span class="shift-tag on">🟢 En turno desde {{ $mucama->shiftLogs->first()->started_at->timezone('America/Santiago')->format('H:i') }}</span>
                @else
                    <span class="shift-tag off">⚪ Fuera de turno</span>
                @endif
                @if (!$mucama->pin)
                    <small style="display:block; color:#e88a9a;">Sin PIN asignado -- no puede entrar al panel</small>
                @endif
            </div>
            <div class="where">
                @if ($mucama->lastQrRoom)
                    <div class="room">{{ $mucama->lastQrRoom->name }} <span style="color:#999; font-weight:500;">· {{ $mucama->lastQrRoom->category->name }}</span></div>
                    <div class="ago">hace {{ $mucama->last_qr_seen_at->diffForHumans(null, true) }}</div>
                    @if ($mucama->lastQrRoom->operational_status === 'aseo' && $mucama->lastQrRoom->aseo_reported_at)
                        <span class="status-tag reported">Reportó, falta confirmar</span>
                    @elseif ($mucama->lastQrRoom->operational_status === 'aseo')
                        <span class="status-tag working">Trabajando en esto</span>
                    @else
                        <span class="status-tag done">Ya liberada</span>
                    @endif
                @else
                    <div class="ago">Todavía no escaneó ninguna habitación</div>
                    <span class="status-tag none">Sin actividad</span>
                @endif
            </div>
        </div>
    @empty
        <p class="empty">No hay mucamas activas cargadas en Personal.</p>
    @endforelse

    <h2 class="section">Registro de turnos de hoy</h2>
    @if ($todayLogs->isEmpty())
        <p class="empty">Todavía no hay entradas registradas hoy.</p>
    @else
        <table class="log-table">
            <thead><tr><th>Mucama</th><th>Entrada</th><th>Salida</th><th>Duración</th></tr></thead>
            <tbody>
                @foreach ($todayLogs as $log)
                    <tr>
                        <td>{{ $log->staff->name }}</td>
                        <td>{{ $log->started_at->timezone('America/Santiago')->format('H:i') }}</td>
                        <td class="{{ $log->ended_at ? '' : 'open' }}">{{ $log->ended_at ? $log->ended_at->timezone('America/Santiago')->format('H:i') : 'En turno' }}</td>
                        <td>{{ intdiv($log->durationMinutes(), 60) }}h {{ str_pad($log->durationMinutes() % 60, 2, '0', STR_PAD_LEFT) }}min</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
</body>
</html>
