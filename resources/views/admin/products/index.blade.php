@extends('admin.layout')
@section('title', 'Productos')
@section('content')
    <div class="actions" style="justify-content:space-between; margin-bottom:18px;">
        <div><h1>Consumo / productos</h1><p class="sub">El listado que recepción ofrece antes de cerrar cada reserva.</p></div>
        <a class="btn" href="{{ route('admin.products.create') }}">+ Nuevo producto</a>
    </div>
    <div class="card">
        <table>
            <thead><tr><th>Categoría</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Reponer</th><th>Estado</th><th></th></tr></thead>
            <tbody>
                @foreach ($products as $product)
                    <tr>
                        <td>{{ $product->category ?? '—' }}</td>
                        <td>{{ $product->name }}</td>
                        <td>${{ number_format($product->price, 0, ',', '.') }}</td>
                        <td>
                            @if (! $product->track_inventory)
                                <span class="pill">SIN CONTROL</span>
                            @elseif ($product->isOutOfStock())
                                <span class="pill pill-bad">AGOTADO</span>
                            @elseif ($product->isLowStock())
                                <span class="pill pill-warn">{{ $product->stock }} — BAJO</span>
                            @else
                                <span class="pill">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if ($product->track_inventory)
                                <form class="stock-form" method="POST" action="{{ route('admin.products.restock', $product) }}">
                                    @csrf
                                    <input type="number" name="delta" placeholder="+10">
                                    <button type="submit">Aplicar</button>
                                </form>
                            @endif
                        </td>
                        <td><span class="pill">{{ $product->is_active ? 'ACTIVO' : 'INACTIVO' }}</span></td>
                        <td><a class="link" href="{{ route('admin.products.edit', $product) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
