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
        .status-badge { display:inline-flex; background:#3a2a12; color:#ffd699; border:1px solid #7a5a1f; border-radius:20px; padding:3px 10px; font-size:11.5px; font-weight:700; }
        .discount-row span:last-child { color:#6ee7b7; }
        .next-steps { background:#fff; border:1px solid #e2e2df; border-radius:10px; padding:14px 16px; margin-bottom:20px; text-align:left; font-size:13px; color:#42464e; }
        .next-steps strong { display:block; margin-bottom:6px; font-size:13.5px; }
        .next-steps ul { margin:0; padding-left:18px; }
        .next-steps li { margin-bottom:4px; }
        a.btn { display:block; background:var(--hh-accent); color:#21170e; text-decoration:none; padding: 13px; border-radius: 9px; font-weight:750; font-size:14.5px; }
        a.btn:hover { background:var(--hh-accent-hover); }
        a.back { display:block; margin-top:14px; color:#62656c; text-decoration:none; font-size:13px; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="check">✓</div>
        <h1>¡Reserva creada!</h1>
        <p class="sub">Guarda el código — te lo vamos a pedir al llegar.</p>

        @if (session('warning'))
            <div class="next-steps" style="background:#3a2a12; border-color:#7a5a1f; color:#ffd699;">{{ session('warning') }}</div>
        @endif

        <div class="card">
            <div class="row"><span class="muted">Código</span><span class="code">{{ $booking->code }}</span></div>
            <div class="row"><span class="muted">Estado</span><span class="status-badge">Pendiente de pago</span></div>
            <div class="row"><span class="muted">Habitación</span><span>{{ $booking->room->name }} · {{ $booking->room->category->name }}</span></div>
            <div class="row"><span class="muted">Fecha</span><span>{{ $booking->starts_at->timezone('America/Santiago')->locale('es')->isoFormat('D [de] MMMM, HH:mm') }}</span></div>
            <div class="row"><span class="muted">Duración</span><span>{{ $booking->duration_minutes >= 60 ? intdiv($booking->duration_minutes, 60).' h' : $booking->duration_minutes.' min' }}</span></div>
            @if ($booking->discount_amount > 0)
                <div class="row discount-row"><span class="muted">Descuento aplicado{{ $booking->coupon_code_snapshot ? ' ('.$booking->coupon_code_snapshot.')' : '' }}</span><span>-${{ number_format($booking->discount_amount, 0, ',', '.') }}</span></div>
            @endif
            @foreach ($booking->addons as $addon)
                <div class="row"><span class="muted">{{ $addon->description }}</span><span>${{ number_format($addon->amount, 0, ',', '.') }}</span></div>
            @endforeach
            <div class="row"><span class="muted">Total a pagar</span><span>${{ number_format($booking->balanceDue(), 0, ',', '.') }}</span></div>
        </div>

        <div class="next-steps">
            <strong>¿Qué sigue ahora?</strong>
            <ul>
                <li>Todavía no cobramos nada — el pago se hace al llegar al motel.</li>
                <li>Esta reserva queda pendiente hasta que la confirmemos por WhatsApp.</li>
                <li>Presenta tu documento de identidad al llegar.</li>
            </ul>
        </div>

        <a class="btn" href="https://wa.me/56977683108?text={{ rawurlencode('Hola! Tengo la reserva '.$booking->code.' en HH.') }}" target="_blank" rel="noopener">Avisar por WhatsApp que ya reservé →</a>
        <a class="back" href="{{ route('catalog.index') }}">← Volver al catálogo</a>
    </div>
</body>
</html>
