@extends('admin.layout')
@section('title', 'Combos')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Combos</h1><p class="sub">Paquetes de productos a un precio especial — "2 cervezas por $5.000", "Combo Deseo", etc.</p></div>
        <a class="btn" href="{{ route('admin.combos.create') }}">+ Nuevo combo</a>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Combo</th><th>Incluye</th><th>Precio</th><th>Stock</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @forelse ($combos as $combo)
                    <tr>
                        <td>{{ $combo->name }}</td>
                        <td style="font-size:12.5px; color:#999;">
                            @forelse ($combo->items as $item)
                                {{ $item->quantity }}x {{ $item->product->name }}@if (! $loop->last), @endif
                            @empty
                                <span style="color:#666;">sin productos aún</span>
                            @endforelse
                        </td>
                        <td>${{ number_format($combo->price, 0, ',', '.') }}</td>
                        <td>
                            @php $avail = $combo->availableCount(); @endphp
                            @if (is_null($avail))
                                <span class="pill">SIN CONTROL</span>
                            @elseif ($avail <= 0)
                                <span class="pill pill-bad">AGOTADO</span>
                            @else
                                <span class="pill">{{ $avail }}</span>
                            @endif
                        </td>
                        <td><span class="pill">{{ $combo->is_active ? 'ACTIVO' : 'INACTIVO' }}</span></td>
                        <td><a class="link" href="{{ route('admin.combos.edit', $combo) }}">Editar</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" style="color:#666;">Todavía no hay combos creados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
