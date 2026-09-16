@extends('admin.layout')
@section('title', $combo->exists ? 'Editar combo' : 'Nuevo combo')
@section('content')
    <h1>{{ $combo->exists ? 'Editar combo' : 'Nuevo combo' }}</h1>
    <p class="sub">{{ $combo->exists ? $combo->name : 'Después de guardar, le agregas los productos que incluye.' }}</p>

    <div class="card">
        <form method="POST" action="{{ $combo->exists ? route('admin.combos.update', $combo) : route('admin.combos.store') }}">
            @csrf
            @if ($combo->exists) @method('PUT') @endif

            <label>Nombre</label>
            <input type="text" name="name" value="{{ old('name', $combo->name) }}" placeholder="Combo Deseo, 2 cervezas por $5.000..." required>

            <label>Descripción (opcional)</label>
            <input type="text" name="description" value="{{ old('description', $combo->description) }}" placeholder="Lo que incluye, en palabras simples">

            <div class="row2">
                <div>
                    <label>Precio del combo (CLP)</label>
                    <input type="number" name="price" min="0" value="{{ old('price', $combo->price ?? 0) }}" required>
                </div>
                <div>
                    <label>Orden de despliegue</label>
                    <input type="number" name="display_order" value="{{ old('display_order', $combo->display_order ?? 0) }}" required>
                </div>
            </div>

            <label>Estado</label>
            <select name="is_active">
                <option value="1" @selected(old('is_active', $combo->is_active ?? true))>Activo</option>
                <option value="0" @selected(!old('is_active', $combo->is_active ?? true))>Inactivo</option>
            </select>

            <div class="actions" style="margin-top:20px;">
                <button class="btn" type="submit">{{ $combo->exists ? 'Guardar' : 'Crear combo' }}</button>
                <a class="link" href="{{ route('admin.combos.index') }}">Cancelar</a>
            </div>
        </form>
    </div>

    @if ($combo->exists)
        <div class="card">
            <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:.04em; color:#999; margin:0 0 12px;">Productos que incluye</h3>
            <table>
                <thead><tr><th>Producto</th><th>Cantidad</th><th></th></tr></thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item->product->name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>
                                <form class="inline" method="POST" action="{{ route('admin.combos.items.destroy', [$combo, $item]) }}" onsubmit="return confirm('¿Quitar este producto del combo?');">
                                    @csrf @method('DELETE')
                                    <button class="btn secondary" type="submit" style="padding:5px 12px; font-size:12px;">Quitar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="color:#666;">Sin productos todavía — agrega el primero abajo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <p class="sub" style="margin-bottom:10px;">Agregar producto al combo</p>
            <form method="POST" action="{{ route('admin.combos.items.store', $combo) }}">
                @csrf
                <div class="row2">
                    <div>
                        <label>Producto</label>
                        <select name="product_id" required>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} (${{ number_format($product->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Cantidad</label>
                        <input type="number" name="quantity" min="1" max="20" value="1" required>
                    </div>
                </div>
                <div class="actions" style="margin-top:16px;">
                    <button class="btn" type="submit">Agregar al combo</button>
                </div>
            </form>
        </div>
    @endif
@endsection
