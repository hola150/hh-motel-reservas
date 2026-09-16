@extends('admin.layout')
@section('title', 'Analytics')
@section('content')
    <style>
        .wrap { max-width:1500px !important; }
        .wrap > .card { display:inline-block; vertical-align:top; width:calc(50% - 10px); margin-right:16px; }
        .wrap > .card:nth-of-type(even) { margin-right:0; }
        .wrap > .card:nth-of-type(odd) { border-top:3px solid #ff7918; }
        .wrap > .card:nth-of-type(even) { border-top:3px solid #6fd39a; }
        .wrap > .flt, .wrap > .proj, .wrap > .kpis { width:100%; }
        @media(max-width:850px){.wrap > .card{display:block;width:100%;margin-right:0}}
        .flt { background:#1c1c1c; border:1px solid #333; border-radius:10px; padding:14px 16px; margin-bottom:18px; }
        .flt-row { display:flex; gap:8px; flex-wrap:wrap; align-items:center; margin-bottom:10px; }
        .flt-row:last-child { margin-bottom:0; }
        .flt .chip { background:#111; border:1px solid #333; border-radius:7px; padding:6px 12px; text-decoration:none; color:#ccc; font-size:12px; }
        .flt .chip.active { border-color:#ff7918; color:#ff7918; }
        .flt .chip:hover { border-color:#ff7918; }
        .flt label { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.03em; margin:0; }
        .flt input[type=date], .flt select { background:#111; border:1px solid #333; color:#eee; border-radius:7px; padding:6px 9px; font-size:12.5px; width:auto; }
        .flt .go { background:#ff7918; color:#fff; border:none; border-radius:7px; padding:7px 14px; font-size:12.5px; font-weight:600; cursor:pointer; }
        .flt .metric-toggle { display:inline-flex; border:1px solid #333; border-radius:7px; overflow:hidden; }
        .flt .metric-toggle a { padding:6px 13px; font-size:12px; text-decoration:none; color:#aaa; background:#111; }
        .flt .metric-toggle a.on { background:#ff7918; color:#fff; }

        .kpis { display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:10px; margin-bottom:18px; }
        .kpi { background:#1c1c1c; border:1px solid #333; border-radius:10px; padding:13px 15px; }
        .kpi .k-label { font-size:10.5px; color:#999; text-transform:uppercase; letter-spacing:.03em; margin-bottom:5px; }
        .kpi .k-value { font-family: ui-monospace, monospace; font-weight:700; font-size:19px; color:#eee; }
        .kpi .k-delta { font-size:11.5px; font-family: ui-monospace, monospace; margin-top:3px; }
        .k-delta.up { color:#6fd39a; } .k-delta.down { color:#e88a9a; } .k-delta.flat { color:#777; }

        .proj { background:#14251c; border:1px solid #2e6e45; border-radius:10px; padding:14px 16px; margin-bottom:18px; }
        .proj h3 { margin:0 0 10px; font-size:12px; text-transform:uppercase; letter-spacing:.04em; color:#8fe0ad; }
        .proj-grid { display:grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap:12px; }
        .proj-grid div span { display:block; font-size:10.5px; color:#9cc7ac; text-transform:uppercase; letter-spacing:.03em; margin-bottom:3px; }
        .proj-grid div b { font-family: ui-monospace, monospace; font-size:17px; color:#eee; }
        .proj-grid div.big b { font-size:22px; color:#8fe0ad; }
        .proj .hint { font-size:11px; color:#7fb894; margin-top:8px; }

        .card h3 { margin:0 0 12px; font-size:13px; text-transform:uppercase; letter-spacing:.04em; color:#999; }
        .bar-row { display:grid; grid-template-columns: 130px 1fr 96px; align-items:center; gap:10px; padding:6px 0; }
        .bar-row .name { font-size:12.5px; color:#ddd; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .bar-row .cat { font-size:10px; color:#888; }
        .bar-track { background:#111; border-radius:5px; height:15px; overflow:hidden; position:relative; }
        .bar-fill { background:#ff7918; height:100%; border-radius:5px; min-width:2px; }
        .bar-fill.alt { background:#6fd39a; }
        .bar-prev { position:absolute; top:0; height:100%; border-right:2px dashed #888; }
        .bar-row .val { font-size:12px; color:#ddd; font-family: ui-monospace, monospace; text-align:right; }
        .bar-row .val small { color:#888; }

        .hour-grid { display:grid; grid-template-columns: repeat(12, 1fr); gap:5px; }
        @media (max-width: 700px) { .hour-grid { grid-template-columns: repeat(6, 1fr); } }
        .hour-cell { text-align:center; }
        .hour-track { background:#111; border-radius:4px; height:64px; display:flex; align-items:flex-end; overflow:hidden; }
        .hour-fill { background:#7fbcdc; width:100%; min-height:2px; }
        .hour-label { font-size:9.5px; color:#888; margin-top:3px; font-family: ui-monospace, monospace; }
        .hour-count { font-size:9.5px; color:#555; }

        .month-row { display:grid; grid-template-columns: 88px 1fr 100px 60px; align-items:center; gap:10px; padding:6px 0; border-top:1px solid #222; }
        .month-row:first-of-type { border-top:none; }
        .month-row .m-name { font-size:12.5px; color:#ddd; text-transform:capitalize; }
        .month-row .m-track { background:#111; border-radius:5px; height:15px; overflow:hidden; }
        .month-row .m-fill { background:#6fd39a; height:100%; border-radius:5px; min-width:2px; }
        .month-row .m-val { font-size:12px; color:#eee; font-family: ui-monospace, monospace; text-align:right; }
        .month-row .m-delta { font-size:11px; font-family: ui-monospace, monospace; text-align:right; }
        .m-delta.up { color:#6fd39a; } .m-delta.down { color:#e88a9a; } .m-delta.flat { color:#777; }
        .month-row .m-count { font-size:10.5px; color:#888; }

        .legend { font-size:11px; color:#888; margin-bottom:8px; }
        .legend .dash { display:inline-block; width:14px; border-top:2px dashed #888; vertical-align:middle; margin:0 3px; }
        .empty { color:#666; font-size:14px; padding: 16px 0; text-align:center; }
    </style>

    @php
        $money = fn ($n) => '$'.number_format($n, 0, ',', '.');
        $metricVal = fn ($n) => $metric === 'revenue' ? $money($n) : number_format($n, 0, ',', '.');
        $qs = fn ($over) => array_merge(['preset' => $preset, 'from' => $preset === 'custom' ? $from : null, 'to' => $preset === 'custom' ? $to : null, 'metric' => $metric, 'category' => $categoryId], $over);
    @endphp

    <h1>Analytics</h1>
    <p class="sub">{{ $rangeLabel }} · viendo por <b>{{ $metricLabel }}</b>{{ $categoryId ? ' · '.$categories->firstWhere('id', $categoryId)?->name : '' }}. No cuenta reservas canceladas.</p>

    <form class="flt" method="GET" action="{{ route('admin.analytics.index') }}">
        <div class="flt-row">
            @foreach (['today' => 'Hoy', 'week' => 'Semana', 'this_month' => 'Mes', '7d' => '7 días', '30d' => '30 días', '90d' => '90 días', 'last_month' => 'Mes pasado', 'this_year' => 'Este año', 'all' => 'Todo'] as $k => $lbl)
                <a class="chip {{ $preset === $k ? 'active' : '' }}" href="{{ route('admin.analytics.index', $qs(['preset' => $k, 'from' => null, 'to' => null])) }}">{{ $lbl }}</a>
            @endforeach
        </div>
        <div class="flt-row">
            <label>Desde</label>
            <input type="date" name="from" value="{{ $preset === 'custom' ? $from : '' }}">
            <label>Hasta</label>
            <input type="date" name="to" value="{{ $preset === 'custom' ? $to : '' }}">
            <input type="hidden" name="metric" value="{{ $metric }}">
            <input type="hidden" name="category" value="{{ $categoryId }}">
            <button class="go" type="submit">Aplicar</button>

            <span style="flex:1"></span>

            <span class="metric-toggle">
                <a class="{{ $metric === 'revenue' ? 'on' : '' }}" href="{{ route('admin.analytics.index', $qs(['metric' => 'revenue'])) }}">Venta</a>
                <a class="{{ $metric === 'count' ? 'on' : '' }}" href="{{ route('admin.analytics.index', $qs(['metric' => 'count'])) }}">Reservas</a>
            </span>

            <select onchange="location.href=this.value">
                <option value="{{ route('admin.analytics.index', $qs(['category' => null])) }}" @selected(! $categoryId)>Todas las categorías</option>
                @foreach ($categories as $cat)
                    <option value="{{ route('admin.analytics.index', $qs(['category' => $cat->id])) }}" @selected($categoryId === $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="kpis">
        @foreach (['reservas' => 'Reservas', 'venta' => 'Venta total', 'ticket' => 'Ticket promedio', 'descuentos' => 'Descuentos dados'] as $k => $lbl)
            @php $kp = $kpis[$k]; @endphp
            <div class="kpi">
                <div class="k-label">{{ $lbl }}</div>
                <div class="k-value">{{ $kp['is_money'] ? $money($kp['value']) : number_format($kp['value'], 0, ',', '.') }}</div>
                <div class="k-delta {{ $kp['delta'] === null ? 'flat' : ($kp['delta'] > 0 ? 'up' : ($kp['delta'] < 0 ? 'down' : 'flat')) }}">
                    @if ($kp['delta'] === null) sin período previo
                    @elseif ($kp['delta'] > 0) ▲ {{ $kp['delta'] }}% vs período anterior
                    @elseif ($kp['delta'] < 0) ▼ {{ abs($kp['delta']) }}% vs período anterior
                    @else = vs período anterior
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="proj">
        <h3>Proyección a fin de mes — {{ $projection['label'] }}</h3>
        <div class="proj-grid">
            <div><span>Venta hasta hoy</span><b>{{ $money($projection['so_far']) }}</b></div>
            <div><span>Ya agendado (resto del mes)</span><b>{{ $money($projection['booked_rest']) }}</b></div>
            <div class="big"><span>Proyección fin de mes</span><b>{{ $money($projection['projected']) }}</b></div>
        </div>
        <p class="hint">Ritmo actual: {{ $money((int) round($projection['so_far'] / $projection['days_elapsed'])) }}/día · día {{ $projection['days_elapsed'] }} de {{ $projection['days_in_month'] }}.</p>
    </div>

    <div class="kpis">
        @foreach (['collected' => 'Cobrado', 'balance' => 'Saldo pendiente', 'addons' => 'Venta de extras'] as $key => $label)
            <div class="kpi"><div class="k-label">{{ $label }}</div><div class="k-value">{{ $money($operations[$key]) }}</div><div class="k-delta flat">Periodo seleccionado</div></div>
        @endforeach
        <div class="kpi"><div class="k-label">Clientes por recurrencia</div><div class="k-value">{{ $segments['frecuente'] }}</div><div class="k-delta flat">Alta recurrencia</div></div>
    </div>

    <div class="card">
        <h3>Estado de las reservas</h3>
        @foreach ($bookingStatuses as $status => $count)<div class="bar-row"><div class="name">{{ str_replace('_',' ', $status) }}</div><div class="bar-track"><div class="bar-fill" style="width:{{ $kpis['reservas']['value'] ? round($count/$kpis['reservas']['value']*100) : 0 }}%"></div></div><div class="val">{{ $count }}</div></div>@endforeach
    </div>

    <div class="card">
        <h3>Medios de pago cobrados</h3>
        @forelse ($paymentMethods as $method => $amount)<div class="bar-row"><div class="name">{{ $method }}</div><div class="bar-track"><div class="bar-fill alt" style="width:{{ $paymentMethods->max() ? round($amount/$paymentMethods->max()*100) : 0 }}%"></div></div><div class="val">{{ $money($amount) }}</div></div>@empty<p class="empty">Sin pagos aprobados en el rango.</p>@endforelse
    </div>

    <div class="card">
        <h3>Extras vendidos</h3>
        @forelse ($extras as $description => $extra)
            <div class="bar-row"><div class="name">{{ $description }}</div><div class="bar-track"><div class="bar-fill" style="width:{{ $extras->max('value') ? round($extra['value']/$extras->max('value')*100) : 0 }}%"></div></div><div class="val">{{ $money($extra['value']) }} <small>· {{ $extra['quantity'] }} u.</small></div></div>
        @empty
            <p class="empty">Sin extras vendidos en el periodo.</p>
        @endforelse
    </div>

    <div class="card">
        <h3>Por mes — últimos 12 ({{ $metricLabel }})</h3>
        @forelse ($monthly['rows'] as $m)
            <div class="month-row">
                <div>
                    <div class="m-name">{{ $m['label'] }}</div>
                    <div class="m-count">{{ $m['count'] }} {{ Str::plural('reserva', $m['count']) }}</div>
                </div>
                <div class="m-track"><div class="m-fill" style="width: {{ $monthly['max'] > 0 ? round($m['value'] / $monthly['max'] * 100) : 0 }}%;"></div></div>
                <div class="m-val">{{ $metricVal($m['value']) }}</div>
                <div class="m-delta {{ $m['delta_pct'] === null ? 'flat' : ($m['delta_pct'] > 0 ? 'up' : ($m['delta_pct'] < 0 ? 'down' : 'flat')) }}">
                    @if ($m['delta_pct'] === null) —
                    @elseif ($m['delta_pct'] > 0) ▲{{ $m['delta_pct'] }}%
                    @elseif ($m['delta_pct'] < 0) ▼{{ abs($m['delta_pct']) }}%
                    @else 0%
                    @endif
                </div>
            </div>
        @empty
            <p class="empty">Sin datos.</p>
        @endforelse
    </div>

    <div class="card">
        <h3>Días de la semana ({{ $metricLabel }})</h3>
        <div class="legend">Barra llena = período actual · <span class="dash"></span> línea punteada = mismo día en el período anterior</div>
        @php $maxWd = max(1, $byWeekday->max(fn ($e) => max($e['value'], $e['prev']))); @endphp
        @foreach ($byWeekday as $e)
            <div class="bar-row">
                <div class="name">{{ $e['label'] }}</div>
                <div class="bar-track">
                    <div class="bar-fill alt" style="width: {{ round($e['value'] / $maxWd * 100) }}%;"></div>
                    @if ($e['prev'] > 0)
                        <div class="bar-prev" style="left: {{ round($e['prev'] / $maxWd * 100) }}%;"></div>
                    @endif
                </div>
                <div class="val">{{ $metricVal($e['value']) }} @if ($e['prev'] > 0)<small>(ant. {{ $metricVal($e['prev']) }})</small>@endif</div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <h3>Mejores días del rango ({{ $metricLabel }})</h3>
        @php $maxTd = max(1, $topDays->max('value')); @endphp
        @forelse ($topDays as $d)
            <div class="bar-row">
                <div class="name">{{ ucfirst($d['date']->locale('es')->isoFormat('ddd D/MM')) }}</div>
                <div class="bar-track"><div class="bar-fill alt" style="width: {{ round($d['value'] / $maxTd * 100) }}%;"></div></div>
                <div class="val">{{ $metricVal($d['value']) }} <small>· {{ $d['count'] }}r</small></div>
            </div>
        @empty
            <p class="empty">Sin reservas en el rango.</p>
        @endforelse
    </div>

    <div class="card">
        <h3>Ventas por hora de inicio ({{ $metricLabel }})</h3>
        @php $maxH = max(1, $byHour->max('value')); @endphp
        @if ($byHour->sum('value') > 0)
            <div class="hour-grid">
                @foreach ($byHour as $e)
                    <div class="hour-cell">
                        <div class="hour-track"><div class="hour-fill" style="height: {{ round($e['value'] / $maxH * 100) }}%;"></div></div>
                        <div class="hour-label">{{ str_pad($e['key'], 2, '0', STR_PAD_LEFT) }}h</div>
                        <div class="hour-count">{{ $metric === 'revenue' ? ($e['value'] > 0 ? '$'.round($e['value']/1000).'k' : '') : $e['value'] }}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="empty">Sin reservas en el rango.</p>
        @endif
    </div>

    <div class="card">
        <h3>Habitaciones ({{ $metricLabel }})</h3>
        @php $maxR = max(1, $byRoom->max('value')); @endphp
        @forelse ($byRoom as $e)
            <div class="bar-row">
                <div>
                    <div class="name">{{ $e['label'] }}</div>
                    <div class="cat">{{ $e['sub'] }}</div>
                </div>
                <div class="bar-track"><div class="bar-fill" style="width: {{ round($e['value'] / $maxR * 100) }}%;"></div></div>
                <div class="val">{{ $metricVal($e['value']) }} <small>· {{ $e['count'] }}r</small></div>
            </div>
        @empty
            <p class="empty">Sin reservas en el rango.</p>
        @endforelse
    </div>
@endsection
