@extends('admin.layout')
@section('title', 'Categorías')
@section('content')
    <style>
        .wing-toggle-row { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
        .wing-toggle { display:inline-flex; align-items:center; gap:9px; background:#1c1c1c; border:1px solid #333; color:#ccc; border-radius:20px; padding:8px 16px 8px 12px; font-size:12.5px; font-weight:600; cursor:pointer; }
        .wing-toggle-dot { width:9px; height:9px; border-radius:50%; background:#555; flex:none; }
        .wing-toggle.on { border-color:#1c9169; background:#0f2b22; color:#6ee7b7; }
        .wing-toggle.on .wing-toggle-dot { background:#34d399; }
        .wing-toggle.off { border-color:#7a2d2d; background:#2a1c1c; color:#e8a2a2; }
        .wing-toggle.off .wing-toggle-dot { background:#e05252; }
        .wing-toggle:hover { border-color:#ff7918; }
        .toggle-group { margin-bottom:16px; }
        .toggle-group:last-child { margin-bottom:0; }
        .toggle-group-label { font-size:11px; color:#888; text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px; }
    </style>

    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Categorías de Playroom</h1><p class="sub">GO, LITE, PLUS, MAX — características y capacidad base.</p></div>
        <a class="btn" href="{{ route('admin.categories.create') }}">+ Nueva categoría</a>
    </div>

    <div class="card">
        <div class="toggle-group">
            <div class="toggle-group-label">Disponibilidad por ala</div>
            <div class="wing-toggle-row">
                <form method="POST" action="{{ route('admin.ala_sur.toggle') }}">
                    @csrf
                    <button type="submit" class="wing-toggle {{ $settings->ala_sur_enabled ? 'on' : 'off' }}">
                        <span class="wing-toggle-dot"></span>
                        Ala Sur {{ $settings->ala_sur_enabled ? 'habilitada' : 'deshabilitada' }}
                    </button>
                </form>
            </div>
        </div>
        <div class="toggle-group">
            <div class="toggle-group-label">Disponibilidad por categoría</div>
            <div class="wing-toggle-row">
                @foreach ($categories as $cat)
                    <form method="POST" action="{{ route('admin.categories.toggle', $cat) }}">
                        @csrf
                        <button type="submit" class="wing-toggle {{ $cat->is_active ? 'on' : 'off' }}">
                            <span class="wing-toggle-dot"></span>
                            {{ $cat->name }} {{ $cat->is_active ? 'habilitada' : 'deshabilitada' }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        <div class="toggle-group">
            <div class="toggle-group-label">Disponibilidad por piso <span style="text-transform:none; letter-spacing:normal;">— 1er (1xx), 2do (2xx), 3er (3xx)</span></div>
            <div class="wing-toggle-row">
                @foreach ([1 => $settings->piso_1_enabled, 2 => $settings->piso_2_enabled, 3 => $settings->piso_3_enabled] as $floor => $enabled)
                    <form method="POST" action="{{ route('admin.floors.toggle', $floor) }}">
                        @csrf
                        <button type="submit" class="wing-toggle {{ $enabled ? 'on' : 'off' }}">
                            <span class="wing-toggle-dot"></span>
                            Piso {{ $floor }} {{ $enabled ? 'habilitado' : 'deshabilitado' }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
        <p class="sub" style="margin:14px 0 0;">Apagar un ala, categoría o piso solo saca sus habitaciones libres de "Disponibles" en el tablero — lo que ya está ocupado, por llegar, en aseo o fuera de servicio no se toca.</p>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Nombre</th><th>Capacidad base</th><th>Cobra extra desde</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @foreach ($categories as $cat)
                    <tr>
                        <td>{{ $cat->name }} @if($cat->former_name)<span style="color:#888;">({{ $cat->former_name }})</span>@endif</td>
                        <td>{{ $cat->base_capacity }}</td>
                        <td>{{ $cat->extra_guest_from }}ª persona</td>
                        <td><span class="pill">{{ $cat->is_active ? 'ACTIVA' : 'INACTIVA' }}</span></td>
                        <td><a class="link" href="{{ route('admin.categories.edit', $cat) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
