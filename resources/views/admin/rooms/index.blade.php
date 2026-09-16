@extends('admin.layout')
@section('title', 'Playrooms')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Playrooms</h1><p class="sub">Inventario real de Playrooms — reemplaza los de ejemplo aquí.</p></div>
        <div class="actions">
            <a class="btn secondary" href="{{ route('catalog.index') }}" target="_blank" rel="noopener">Ver catálogo público ↗</a>
            <a class="btn" href="{{ route('admin.rooms.create') }}">+ Nuevo Playroom</a>
        </div>
    </div>
    <div class="card" style="margin-bottom:18px; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
        <div>
            <strong style="display:block; margin-bottom:2px;">Link para compartir con clientes</strong>
            <span class="sub" style="margin:0;">{{ route('catalog.index') }}</span>
        </div>
        <button type="button" class="btn secondary" onclick="navigator.clipboard.writeText('{{ route('catalog.index') }}'); this.textContent='¡Copiado!'; setTimeout(() => this.textContent='Copiar link', 1500);">Copiar link</button>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Nombre</th><th>Categoría</th><th>Buffer aseo</th><th>Estado</th><th>Fotos</th><th></th></tr></thead>
            <tbody>
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->category->name }}</td>
                        <td>{{ $room->buffer_minutes }} min</td>
                        <td><span class="pill">{{ strtoupper($room->operational_status) }}</span></td>
                        <td>
                            @if ($room->photos)
                                <a href="{{ route('admin.rooms.edit', $room) }}" class="hh-room-thumb-link">
                                    <img src="{{ $room->photos[0] }}" alt="" class="hh-room-thumb">
                                    <span>{{ count($room->photos) }}</span>
                                </a>
                            @else
                                <span class="sub" style="font-size:12px;">—</span>
                            @endif
                        </td>
                        <td><a class="link" href="{{ route('admin.rooms.edit', $room) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
