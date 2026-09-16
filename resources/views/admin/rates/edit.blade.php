@extends('admin.layout')
@section('title', 'Tarifa '.$rateRule->name)
@section('content')
    <h1>Tarifa {{ $rateRule->name }}</h1>
    <p class="sub">{{ $rateRule->description }}</p>

    <div class="card">
        <form method="POST" action="{{ route('admin.rates.update', $rateRule) }}">
            @csrf @method('PUT')
            <div class="row2">
                <div>
                    <label>Adicional por persona (desde la 3ª, CLP)</label>
                    <input type="number" name="extra_person_price" min="0" value="{{ old('extra_person_price', $rateRule->extra_person_price) }}" required>
                </div>
                <div>
                    <label>Prioridad (desempate si dos tarifas compiten)</label>
                    <input type="number" name="priority" value="{{ old('priority', $rateRule->priority) }}" required>
                </div>
            </div>
            <label>Estado</label>
            <select name="is_active">
                <option value="1" @selected($rateRule->is_active)>Activa</option>
                <option value="0" @selected(!$rateRule->is_active)>Inactiva</option>
            </select>
            <div class="actions" style="margin-top:16px;"><button class="btn" type="submit">Guardar</button></div>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Categoría</th><th>Duración</th><th>Precio</th><th></th></tr></thead>
            <tbody>
                @foreach ($prices as $price)
                    <tr>
                        <td>{{ $price->roomCategory->name }}</td>
                        <td>{{ $price->duration_minutes / 60 }} h</td>
                        <td>
                            <form class="inline" method="POST" action="{{ route('admin.rates.prices.update', $price) }}" style="display:flex; gap:6px;">
                                @csrf @method('PUT')
                                <input type="number" name="price" value="{{ $price->price }}" min="0" style="width:120px;">
                                <button class="btn secondary" type="submit" style="padding:6px 12px; font-size:12.5px;">Guardar</button>
                            </form>
                        </td>
                        <td>
                            <form class="inline" method="POST" action="{{ route('admin.rates.prices.destroy', $price) }}" onsubmit="return confirm('¿Eliminar este precio?');">
                                @csrf @method('DELETE')
                                <button class="btn secondary" type="submit" style="padding:6px 12px; font-size:12.5px;">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card">
        <p class="sub" style="margin-bottom:10px;">Agregar precio para una nueva combinación categoría/duración</p>
        <form method="POST" action="{{ route('admin.rates.prices.store', $rateRule) }}">
            @csrf
            <div class="row2">
                <div>
                    <label>Categoría</label>
                    <select name="room_category_id" required>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Duración (minutos)</label>
                    <input type="number" name="duration_minutes" min="15" step="15" placeholder="180" required>
                </div>
                <div>
                    <label>Precio (CLP)</label>
                    <input type="number" name="price" min="0" required>
                </div>
            </div>
            <div class="actions" style="margin-top:16px;"><button class="btn" type="submit">Agregar</button></div>
        </form>
    </div>

    <a class="link" href="{{ route('admin.rates.index') }}">← Volver a tarifas</a>
@endsection
