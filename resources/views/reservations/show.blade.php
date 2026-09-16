<!doctype html>
<html lang="es" style="background:#111;color:#eee">
<head>
    <meta charset="utf-8"><meta name="color-scheme" content="dark">
    <title>Reserva {{ $booking->code }} — HH Motel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; }
        .page-inner { max-width: 960px; margin: 0; padding: 24px 24px 60px; }
        h1 { font-size: 18px; letter-spacing: .04em; margin-bottom: 2px; }
        .code { font-family: ui-monospace, monospace; color:#ff7918; font-size: 15px; margin-bottom: 20px; }
        .banner { border-radius:10px; padding: 12px 16px; margin-bottom:16px; font-size:14px; }
        .banner.ok { background:#1c2f1c; border:1px solid #2e5a2e; color:#6fd39a; }
        .banner.error { background:#3a1c1c; border:1px solid #7a2d2d; color:#f3b8b8; }

        .layout { display:grid; grid-template-columns: 1fr 1fr; gap:20px; align-items:start; }
        @media (max-width: 760px) { .layout { grid-template-columns: 1fr; } }
        .col { display:flex; flex-direction:column; gap:16px; }

        .card { background:#1c1c1c; border:1px solid #333; border-radius: 10px; padding: 18px 20px; }
        .row { display:flex; justify-content:space-between; padding: 7px 0; font-size: 14.5px; border-bottom: 1px solid #292929; gap:12px; }
        .row:last-child { border-bottom: none; }
        .row.total { font-weight: 700; font-size: 16px; color:#fff; }
        .row .muted { color:#999; flex:none; }
        .pill { display:inline-block; font-size:11px; font-family: ui-monospace, monospace; padding:3px 9px; border-radius:20px; background:#2a2a2a; color:#ccc; }
        .balance { color:#ff7918; }
        a.back { display:block; text-align:center; margin-top: 14px; color:#999; font-size: 13px; text-decoration:none; }
        /* Secundario a propósito: "Finalizar" (arriba) es naranja porque es
           la acción principal de esta pantalla -- volver al tablero es solo
           navegación, no debería competir visualmente con eso. */
        a.board-btn { display:block; text-align:center; background:#2a2a2a; border:1px solid #444; color:#eee; padding:13px; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; margin-top:24px; }
        a.board-btn:hover { border-color:#ff7918; color:#ff7918; }
        a.pay-btn { display:block; text-align:center; background:#ff7918; color:#fff; padding:13px; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; }
        a.pay-btn-inline { margin-top:14px; }
        table.payments { width:100%; border-collapse: collapse; font-size: 13px; }
        table.payments th { text-align:left; color:#888; font-weight:500; font-size:11px; text-transform:uppercase; padding-bottom:6px; }
        table.payments td { padding: 6px 0; border-top: 1px solid #292929; }
        .addon-form { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
        .addon-form input, .addon-form select { background:#111; border:1px solid #333; color:#fff; padding:8px 10px; border-radius:6px; font-size:13px; min-width:0; }
        .addon-form select { flex:1 1 140px; }
        .addon-form input[name="quantity"] { flex:0 1 60px; }
        .addon-form input[name="description"] { flex:1 1 140px; }
        .addon-form input[name="amount"] { flex:0 1 90px; }
        .addon-form button { width:auto; margin-top:0; padding:8px 14px; font-size:13px; background:#ff7918; color:#fff; border:none; border-radius:6px; font-weight:600; cursor:pointer; flex:0 0 auto; }
        .addon-sep { font-size:11px; color:#777; margin: 12px 0 6px; text-transform:uppercase; letter-spacing:.04em; }
        #consumo { border-left: 3px solid #ff7918; }
        .addon-label { font-size:12.5px; color:#bbb; margin: 4px 0 6px; }
        .pending-alert { background:#3a1c26; border:1px solid #7a2d4a; color:#f3a8c4; padding:10px 14px; border-radius:8px; font-size:13.5px; margin: 4px 0 12px; display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap; }
        .pending-alert a { color:#fff; background:#ff7918; text-decoration:none; font-weight:600; padding:6px 12px; border-radius:6px; font-size:12.5px; white-space:nowrap; }
        ul.guests { margin:0; padding-left:18px; font-size:14px; }
        a.finalize-btn { display:block; text-align:center; margin-top: 16px; background:#2a2a2a; border:1px solid #444; color:#eee; padding:13px; border-radius:8px; font-size:14px; font-weight:600; text-decoration:none; }
        .finish-blocked { margin-top:16px; padding:14px 16px; border:1px solid #b87822; border-radius:8px; background:#fff4df; color:#74480f; display:flex; flex-direction:column; gap:4px; text-align:center; }
        .finish-blocked span { font-size:13px; }
        .checkin-btn { display:block; text-align:center; background:#1c2f3a; border:1px solid #3a5a72; color:#7fbcdc; padding:13px; border-radius:8px; font-size:14px; font-weight:600; width:100%; cursor:pointer; text-decoration:none; }
        .cancel-form { background:#241717; border:1px solid #4a2a2a; border-radius:8px; padding:12px 14px; }
        .cancel-form input { background:#1c1414; border:1px solid #4a2a2a; color:#fff; }
        .cancel-form button { width:100%; margin-top:8px; background:#7a2d2d; color:#fff; border:none; padding:11px; border-radius:7px; font-size:13.5px; font-weight:600; cursor:pointer; }
        .cancel-form button:hover { background:#93393a; }
        .delay-late { color:#e88a9a; }
        .delay-early { color:#6fd39a; }
        .col-actions { display:flex; justify-content:flex-end; align-items:flex-start; gap:8px; margin-bottom:12px; flex-wrap:wrap; }
        .col-btn { display:inline-block; background:#2a2a2a; border:1px solid #444; color:#eee; padding:8px 14px; border-radius:7px; font-size:12.5px; font-weight:600; text-decoration:none; cursor:pointer; white-space:nowrap; list-style:none; }
        .col-btn:hover { border-color:#ff7918; color:#ff7918; }
        .col-btn::-webkit-details-marker { display:none; }
        details.col-cancel { flex:0 0 auto; }
        details.col-cancel[open] { flex:1 1 100%; }
        details.col-cancel .col-btn { background:transparent; border-color:#4a2a2a; color:#c47a7a; }
        details.col-cancel .col-btn:hover { border-color:#a83a3a; color:#e88a8a; }
        details.col-cancel[open] .col-btn { border-color:#a83a3a; color:#e88a8a; margin-bottom:8px; }
    </style>
</head>
<body>
    @include('partials.navbar')
    <div class="page-inner">
    <h1>HH MOTEL — Reserva creada</h1>
    <div class="code">{{ $booking->code }}</div>
    <div class="col-actions"><a class="col-btn" href="{{ route('bookings.pass.preview', $booking->code) }}" target="_blank">Ver pase PDF</a><a class="col-btn" href="{{ route('bookings.pass.pdf', $booking->code) }}">Descargar pase</a></div>

    @if (session('status'))
        <div class="banner ok">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="banner error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="layout">
        {{-- ===== Columna izquierda: la estadía ===== --}}
        <div class="col">
            <div class="card">
                <div class="row"><span class="muted">Habitación</span><span>{{ $booking->room->name }} ({{ $booking->room->category->name }})</span></div>
                @php $custSegment = $booking->customer->segment(); @endphp
                <div class="row">
                    <span class="muted">Cliente</span>
                    <span>
                        <a class="code-link" style="color:#ff7918; text-decoration:none;" href="{{ route('admin.customers.show', $booking->customer) }}" target="_blank" rel="noopener">{{ $booking->customer->name }}</a>
                        <span class="pill" style="margin-left:6px;">{{ strtoupper($custSegment['label']) }}</span>
                    </span>
                </div>
                <div class="row"><span class="muted">Teléfono</span><span>{{ $booking->customer->phone_e164 }}</span></div>
                <div class="row"><span class="muted">Fecha / hora</span><span>{{ $booking->starts_at->timezone('America/Santiago')->format('d/m/Y H:i') }}</span></div>
                <div class="row"><span class="muted">Duración</span><span>{{ $booking->duration_minutes / 60 }} h</span></div>
                <div class="row"><span class="muted">Tarifa aplicada</span><span class="pill">{{ $booking->rate_rule_name_snapshot }}</span></div>
                @if ($booking->checked_in_at)
                    @php $delay = $booking->checkInDelayMinutes(); @endphp
                    <div class="row">
                        <span class="muted">Check-in real</span>
                        <span>
                            {{ $booking->checked_in_at->timezone('America/Santiago')->format('H:i') }}
                            @if ($delay > 0)
                                <span class="delay-late">({{ $delay }} min tarde)</span>
                            @elseif ($delay < 0)
                                <span class="delay-early">({{ abs($delay) }} min antes)</span>
                            @else
                                (a tiempo)
                            @endif
                        </span>
                    </div>
                @endif
                @if ($booking->checked_out_at)
                    <div class="row"><span class="muted">Check-out real</span><span>{{ $booking->checked_out_at->timezone('America/Santiago')->format('H:i') }}</span></div>
                @endif
            </div>

            @if (! $booking->checked_in_at && ! in_array($booking->booking_status, ['CANCELADA', 'EXPIRADA', 'NO_SHOW', 'FINALIZADA']))
                <a class="checkin-btn" href="{{ route('bookings.checkin.show', $booking->code) }}">Marcar check-in — ofrecer consumo →</a>
            @endif

            @if ($booking->guests->isNotEmpty())
                <div class="card">
                    <div class="row" style="border-bottom:none; padding-bottom:4px;"><span class="muted">Acompañantes</span><span></span></div>
                    <ul class="guests">
                        @foreach ($booking->guests as $guest)
                            <li>{{ $guest->name }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card">
                <div class="row"><span class="muted">Estado reserva</span><span class="pill">{{ $booking->booking_status }}</span></div>
                <div class="row"><span class="muted">Estado pago</span><span class="pill">{{ $booking->payment_status }}</span></div>
            </div>
        </div>

        {{-- ===== Columna derecha: el dinero ===== --}}
        <div class="col">
            <div class="col-actions">
                <a class="col-btn" href="{{ route('rooms.board') }}">← Tablero</a>
                @unless (in_array($booking->booking_status, ['CANCELADA', 'EXPIRADA', 'NO_SHOW', 'FINALIZADA']))
                    <a class="col-btn" href="{{ route('reservations.edit', $booking->code) }}">Modificar</a>
                @endunless
                @unless (in_array($booking->booking_status, ['FINALIZADA', 'CANCELADA', 'EXPIRADA', 'NO_SHOW']))
                    <details class="col-cancel">
                        <summary class="col-btn">Cancelar</summary>
                        <form class="cancel-form" method="POST" action="{{ route('bookings.cancel', $booking->code) }}" onsubmit="return confirm('¿Cancelar esta reserva? La habitación queda libre de inmediato.');">
                            @csrf
                            <label style="display:block; font-size:12px; color:#bbb; margin-bottom:6px;">Motivo (opcional)</label>
                            <input type="text" name="reason" placeholder="Cliente no llegó, error de carga, cambio de planes...">
                            <button type="submit">Confirmar cancelación</button>
                        </form>
                    </details>
                @endunless
            </div>
            <div class="card">
                <div class="row"><span class="muted">Precio original</span><span>${{ number_format($booking->price_original, 0, ',', '.') }}</span></div>
                @if ($booking->coupon_code_snapshot)
                    <div class="row"><span class="muted">Promoción ({{ $booking->coupon_code_snapshot }})</span><span>-${{ number_format($booking->discount_amount, 0, ',', '.') }}</span></div>
                @endif
                <div class="row total"><span>Precio final (habitación)</span><span>${{ number_format($booking->price_final, 0, ',', '.') }}</span></div>
                @if ($booking->addonsTotal() > 0)
                    <div class="row"><span class="muted">Consumo</span><span>${{ number_format($booking->addonsTotal(), 0, ',', '.') }}</span></div>
                @endif
                <div class="row"><span class="muted">Abono objetivo</span><span>${{ number_format($booking->deposit_amount, 0, ',', '.') }}</span></div>
                <div class="row"><span class="muted">Pagado</span><span>${{ number_format($booking->paidAmount(), 0, ',', '.') }}</span></div>
                <div class="row total balance"><span>Saldo</span><span>${{ number_format($booking->balanceDue(), 0, ',', '.') }}</span></div>
                @if ($booking->balanceDue() > 0)
                    <a class="pay-btn pay-btn-inline" href="{{ route('payments.create', $booking->code) }}">Registrar pago del saldo →</a>
                @endif
            </div>

            @php $consumo = $booking->addonsByPaymentStatus(); @endphp

            <div class="card" id="consumo">
                <div class="row" style="border-bottom:none; padding-bottom:4px;"><span class="muted">Consumo / extras</span><span></span></div>

                @if ($booking->balanceDue() > 0)
                    <div class="pending-alert">
                        <span><strong>Saldo pendiente:</strong> ${{ number_format($booking->balanceDue(), 0, ',', '.') }}</span>
                        <small>Se cobra desde el botón superior</small>
                    </div>
                @endif

                @if ($consumo['paid']->isNotEmpty())
                    <div class="addon-sep" style="margin-top:2px;">Ya pagado</div>
                    @foreach ($consumo['paid'] as $addon)
                        <div class="row" style="opacity:.6;"><span>✓ {{ $addon->description }}</span><span>${{ number_format($addon->amount, 0, ',', '.') }}</span></div>
                    @endforeach
                @endif

                @if ($consumo['pending']->isNotEmpty())
                    <div class="addon-sep" style="color:#ff7918;">Orden nueva — pendiente de pago</div>
                    @foreach ($consumo['pending'] as $addon)
                        <div class="row"><span>{{ $addon->description }}</span><span>${{ number_format($addon->amount, 0, ',', '.') }}</span></div>
                    @endforeach
                @endif

                <div class="addon-label">Agregar producto o combo</div>
                <form method="POST" action="{{ route('addons.extra-hour', $booking->code) }}" style="margin:10px 0 14px;">
                    @csrf
                    <button type="submit" style="width:100%;background:#f0b84b;color:#241b08;border:none;padding:10px;border-radius:7px;font-weight:700;cursor:pointer;">Vender 1 hora adicional — ${{ number_format($extraHourPrice, 0, ',', '.') }}</button>
                </form>
                <form class="addon-form" method="POST" action="{{ route('addons.store', $booking->code) }}">
                    @csrf
                    <select name="item" required>
                        <option value="" disabled selected>— Elegí un producto o combo —</option>
                        @if ($combos->isNotEmpty())
                            <optgroup label="Combos">
                                @foreach ($combos as $combo)
                                    <option value="combo:{{ $combo->id }}" @disabled($combo->isOutOfStock())>
                                        {{ $combo->name }} — ${{ number_format($combo->price, 0, ',', '.') }}
                                        {{ $combo->isOutOfStock() ? '(agotado)' : '' }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        <optgroup label="Productos individuales">
                            @foreach ($products as $product)
                                <option value="product:{{ $product->id }}" @disabled($product->isOutOfStock())>
                                    {{ $product->name }} — ${{ number_format($product->price, 0, ',', '.') }}
                                    @if ($product->track_inventory)
                                        {{ $product->isOutOfStock() ? '(agotado)' : '('.$product->stock.' disp.)' }}
                                    @endif
                                </option>
                            @endforeach
                        </optgroup>
                    </select>
                    <input type="number" name="quantity" value="1" min="1" max="20">
                    <button type="submit">Agregar</button>
                </form>
                <div class="addon-sep">O algo que no está en la lista</div>
                <form class="addon-form" method="POST" action="{{ route('addons.store', $booking->code) }}">
                    @csrf
                    <input type="text" name="description" placeholder="Descripción">
                    <input type="number" name="amount" placeholder="$" min="1">
                    <button type="submit">Agregar</button>
                </form>
            </div>

            @if ($booking->payments->isNotEmpty())
                <div class="card">
                    <table class="payments">
                        <thead><tr><th>Método</th><th>Monto</th><th>Voucher</th><th>Boleta</th><th>Estado</th></tr></thead>
                        <tbody>
                            @foreach ($booking->payments as $payment)
                                <tr>
                                    <td>{{ $payment->paymentMethod->name }}</td>
                                    <td>${{ number_format($payment->amount, 0, ',', '.') }}</td>
                                    <td>{{ $payment->voucher_number ?? '—' }}</td>
                                    <td>{{ $payment->receipt_number ?? '—' }}</td>
                                    <td>{{ $payment->status }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($booking->checked_in_at && $booking->balanceDue() <= 0 && ! in_array($booking->booking_status, ['FINALIZADA', 'CANCELADA', 'EXPIRADA']))
        <a class="finalize-btn" href="{{ route('bookings.finalize.show', $booking->code) }}">Finalizar reserva — marcar check-out →</a>
        <p class="hint" style="text-align:center;">Marca la salida ahora mismo, aunque sea antes de la hora reservada.</p>
    @elseif ($booking->checked_in_at && $booking->balanceDue() > 0 && ! in_array($booking->booking_status, ['FINALIZADA', 'CANCELADA', 'EXPIRADA']))
        <div class="finish-blocked"><strong>No se puede finalizar todavía</strong><span>Registra el saldo pendiente de ${{ number_format($booking->balanceDue(), 0, ',', '.') }} antes de marcar el check-out.</span></div>
    @elseif (! $booking->checked_in_at && ! in_array($booking->booking_status, ['FINALIZADA', 'CANCELADA', 'EXPIRADA', 'NO_SHOW']))
        <p class="sub" style="text-align:center; margin-top:16px;">Falta marcar el check-in antes de poder finalizar esta reserva.</p>
    @endif


    <a class="board-btn" href="{{ route('rooms.board') }}">← Volver al tablero</a>
    <a class="back" href="{{ route('reservations.create') }}">+ Nueva reserva</a>
    </div>
</body>
</html>
