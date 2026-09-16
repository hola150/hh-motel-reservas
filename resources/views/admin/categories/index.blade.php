@extends('admin.layout')
@section('title', 'Categorías')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Categorías de Playroom</h1><p class="sub">GO, LITE, PLUS, MAX — características y capacidad base.</p></div>
        <a class="btn" href="{{ route('admin.categories.create') }}">+ Nueva categoría</a>
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
