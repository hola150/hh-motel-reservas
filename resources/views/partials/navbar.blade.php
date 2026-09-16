<style>
    .hh-nav-wrap { position: sticky; top: 0; z-index: 100; }
    .hh-navbar { display:flex; align-items:center; gap:6px; padding:14px 24px; border-bottom:1px solid #292929; flex-wrap:wrap; background:#111; }
    .hh-navbar .hh-brand { font-size:14px; font-weight:700; letter-spacing:.03em; color:#eee; margin-right:10px; white-space:nowrap; }
    .hh-navbar a { color:#aaa; text-decoration:none; font-size:13px; padding:6px 11px; border-radius:6px; }
    .hh-navbar a.hh-active, .hh-navbar a:hover { background:#222; color:#fff; }
    .hh-navbar .hh-spacer { flex:1; }
    .hh-navbar .hh-new-btn { background:#ff7918; color:#fff !important; padding:9px 16px; border-radius:7px; font-size:13.5px; font-weight:600; white-space:nowrap; }
    .hh-navbar .hh-new-btn:hover { background:#df6209; }
    .hh-tariff-chip {
        display:flex; align-items:center; gap:7px; white-space:nowrap;
        background:#1c2f1c; border:1px solid #2e5a2e; border-radius:8px;
        padding:9px 13px; font-size:12px;
    }
    .hh-tariff-chip.hh-closed { background:#2a2a1c; border-color:#5a5230; }
    .hh-tariff-chip .hh-tag {
        font-family: ui-monospace, monospace; font-weight:700; font-size:11px;
        padding:2px 8px; border-radius:20px; background:#2e6e45; color:#eafff0;
    }
    .hh-tariff-chip.hh-closed .hh-tag { background:#93641a; color:#fff3d6; }
    .hh-tariff-chip .hh-when { color:#aaa; }
    .hh-subnav { display:flex; gap:4px; padding:10px 24px; border-bottom:1px solid #222; flex-wrap:wrap; background:#161616; }
    .hh-subnav a { color:#888; text-decoration:none; font-size:12.5px; padding:5px 10px; border-radius:6px; }
    .hh-subnav a.hh-active, .hh-subnav a:hover { background:#222; color:#eee; }
    .hh-navbar a.hh-danger { color:#a87878; }
    .hh-navbar a.hh-danger:hover { background:#2e1c1c; color:#e88a8a; }
</style>
<link rel="stylesheet" href="{{ asset('css/hh-theme.css') }}?v={{ filemtime(public_path('css/hh-theme.css')) }}">
@include('partials.sidebar')
<div class="hh-nav-wrap">
    <div class="hh-navbar">
        <span class="hh-brand">HH MOTEL</span>
        <a href="{{ route('rooms.board') }}" class="{{ request()->routeIs('rooms.board') ? 'hh-active' : '' }}">Tablero</a>
        <a href="{{ route('reservations.search') }}" class="{{ request()->routeIs('reservations.search') ? 'hh-active' : '' }}">Buscar reserva</a>
        <a href="{{ route('sales.daily') }}" class="{{ request()->routeIs('sales.daily') ? 'hh-active' : '' }}">Venta de hoy</a>
        <a href="{{ route('cash.show') }}" class="{{ request()->routeIs('cash.show') ? 'hh-active' : '' }}">Caja</a>
        <a href="{{ route('aseo.daily') }}" class="{{ request()->routeIs('aseo.daily') ? 'hh-active' : '' }}">Aseo del día</a>
        <a href="{{ route('rooms.inspections.panel') }}" class="{{ request()->routeIs('rooms.inspections.*') ? 'hh-active' : '' }}">Mantención</a>
        <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.*') ? 'hh-active' : '' }}">Admin</a>
        <span class="hh-spacer"></span>
        @stack('nav-before-tariff')
        @if (isset($showTariff) && $showTariff)
            @if ($tariff)
                <div class="hh-tariff-chip">
                    <span class="hh-tag">{{ $tariff['rate_rule']->name }}</span>
                    <span class="hh-when">cierra en {{ $closesLabel }}</span>
                </div>
            @else
                <div class="hh-tariff-chip hh-closed">
                    <span class="hh-tag">FUERA DE HORARIO</span>
                    <span class="hh-when">@if($nextOpeningLabel) abre en {{ $nextOpeningLabel }} @else sin tarifas @endif</span>
                </div>
            @endif
        @endif
        @stack('nav-actions')
        <a class="hh-new-btn" href="{{ route('reservations.create') }}">+ Nueva reserva</a>
    </div>
    @if (request()->routeIs('admin.*'))
        <div class="hh-subnav">
            <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'hh-active' : '' }}">Categorías</a>
            <a href="{{ route('admin.rooms.index') }}" class="{{ request()->routeIs('admin.rooms.*') ? 'hh-active' : '' }}">Habitaciones</a>
            <a href="{{ route('admin.furniture.index') }}" class="{{ request()->routeIs('admin.furniture.*') ? 'hh-active' : '' }}">Mobiliario</a>
            <a href="{{ route('admin.rates.index') }}" class="{{ request()->routeIs('admin.rates.*') ? 'hh-active' : '' }}">Tarifas</a>
            <a href="{{ route('admin.offers.index') }}" class="{{ request()->routeIs('admin.offers.*') ? 'hh-active' : '' }}">Ofertas</a>
            <a href="{{ route('admin.upsells.index') }}" class="{{ request()->routeIs('admin.upsells.*') ? 'hh-active' : '' }}">Upsells</a>
            <a href="{{ route('admin.coupons.index') }}" class="{{ request()->routeIs('admin.coupons.*') ? 'hh-active' : '' }}">Cupones</a>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'hh-active' : '' }}">Productos</a>
            <a href="{{ route('admin.combos.index') }}" class="{{ request()->routeIs('admin.combos.*') ? 'hh-active' : '' }}">Combos</a>
            <a href="{{ route('admin.customers.index') }}" class="{{ request()->routeIs('admin.customers.*') ? 'hh-active' : '' }}">Clientes</a>
            <a href="{{ route('admin.analytics.index') }}" class="{{ request()->routeIs('admin.analytics.*') ? 'hh-active' : '' }}">Analytics</a>
            <a href="{{ route('admin.staff.index') }}" class="{{ request()->routeIs('admin.staff.*') ? 'hh-active' : '' }}">Personal</a>
            <a href="{{ route('admin.shifts.index') }}" class="{{ request()->routeIs('admin.shifts.*') ? 'hh-active' : '' }}">Turnos</a>
        </div>
    @endif
</div>
