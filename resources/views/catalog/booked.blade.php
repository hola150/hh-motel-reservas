<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="light">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva confirmada — HH Motel</title>
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
        .wrap { max-width: 440px; margin: 0 auto; padding: 60px 20px; text-align:center; }
        .check { width:56px; height:56px; border-radius:50%; background:#1c3a2a; color:#6fd39a; display:flex; align-items:center; justify-content:center; font-size:26px; font-weight:800; margin: 0 auto 18px; }
        h1 { font-size: 21px; margin: 0 0 8px; letter-spacing:-.02em; }
        p.sub { color:#62656c; font-size: 14px; margin: 0 0 26px; }
        .card { background:var(--hh-surface); color:#f5f5f5; border-radius: 12px; box-shadow: 0 3px 10px #1011120b; padding: 20px 22px; text-align:left; margin-bottom: 22px; }
        .row { display:flex; justify-content:space-between; padding: 7px 0; font-size: 14px; border-bottom: 1px solid #505154; }
        .row:last-child { border-bottom:none; }
        .row span.muted { color:#c1c3c7; }
        .code { font-family: ui-monospace, monospace; color:var(--hh-accent); font-size: 16px; font-weight:700; }
        a.btn { display:block; background:var(--hh-accent); color:#21170e; text-decoration:none; padding: 13px; border-radius: 9px; font-weight:750; font-size:14.5px; }
        a.btn:hover { background:var(--hh-accent-hover); }
        a.back { display:block; margin-top:14px; color:#62656c; text-decoration:none; font-size:13px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="check">✓</div>
        <h1>¡Reserva creada!</h1>
        <p class="sub">Te esperamos — presentá tu documento de identidad al llegar.</p>

        <div class="card">
            <div class="row"><span class="muted">Código</span><span class="code">{{ $booking->code }}</span></div>
            <div class="row"><span class="muted">Habitación</span><span>{{ $booking->room->category->name }}</span></div>
            <div class="row"><span class="muted">Fecha</span><span>{{ $booking->starts_at->timezone('America/Santiago')->locale('es')->isoFormat('D [de] MMMM, HH:mm') }}</span></div>
            <div class="row"><span class="muted">Duración</span><span>{{ $booking->duration_minutes >= 60 ? intdiv($booking->duration_minutes, 60).' h' : $booking->duration_minutes.' min' }}</span></div>
            <div class="row"><span class="muted">Total</span><span>${{ number_format($booking->price_final, 0, ',', '.') }}</span></div>
        </div>

        <a class="btn" href="https://wa.me/56977683108?text={{ rawurlencode('Hola! Tengo la reserva '.$booking->code.' en HH Motel.') }}" target="_blank" rel="noopener">Avisar por WhatsApp que ya reservé →</a>
        <a class="back" href="{{ route('catalog.index') }}">← Volver al catálogo</a>
    </div>
</body>
</html>
