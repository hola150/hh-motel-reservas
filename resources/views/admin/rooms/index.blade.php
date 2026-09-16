@extends('admin.layout')
@section('title', 'Playrooms')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Playrooms</h1><p class="sub">Inventario real de Playrooms — reemplaza los de ejemplo aquí.</p></div>
        <a class="btn" href="{{ route('admin.rooms.create') }}">+ Nuevo Playroom</a>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Nombre</th><th>Categoría</th><th>Buffer aseo</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->name }}</td>
                        <td>{{ $room->category->name }}</td>
                        <td>{{ $room->buffer_minutes }} min</td>
                        <td><span class="pill">{{ strtoupper($room->operational_status) }}</span></td>
                        <td><a class="link" href="{{ route('admin.rooms.edit', $room) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
