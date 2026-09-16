@extends('admin.layout')
@section('title', 'Mobiliario')
@section('content')
    <h1>Mobiliario y equipamiento</h1>
    <p class="sub">Organiza el catálogo por categorías. Asigna después los elementos a cada habitación desde su ficha.</p>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin:18px 0 24px;">
        @foreach([['Tipos de mobiliario',$summary['types']],['Unidades asignadas',$summary['units']],['Habitaciones con mobiliario',$summary['rooms']],['Elementos sin asignar',$summary['unassigned']]] as [$label,$value])
            <div class="card" style="padding:14px 16px;"><small style="display:block;color:#777;text-transform:uppercase;font-size:10px;letter-spacing:.06em;">{{ $label }}</small><strong style="display:block;font-size:25px;margin-top:5px;">{{ $value }}</strong></div>
        @endforeach
    </div>
    <h2>Equipamiento por habitación</h2>
    <p class="sub">Vista rápida de lo que encontrará recepción en cada habitación. Estos mismos íconos aparecen en el tablero.</p>
    @if($roomSummary->isNotEmpty())
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:12px;margin:0 0 26px;">
            @foreach($roomSummary as $room)
                <div class="card" style="padding:14px 16px;">
                    <strong style="font-size:16px;">{{ $room->name }}</strong>
                    <small style="display:block;color:#888;margin:3px 0 10px;">{{ $room->category->name }}</small>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;">
                        @foreach($room->furniture->where('pivot.quantity','>',0) as $equipment)
                            <span style="padding:5px 8px;background:#f1f1f1;border-radius:6px;font-size:12px;">{{ $equipment->icon ?? '✦' }} {{ $equipment->name }} ×{{ $equipment->pivot->quantity }}</span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="hh-empty-equipment">Todavía no hay mobiliario asignado a habitaciones.</p>
    @endif
    <div class="hh-catalog-forms">
        <form class="card" method="POST" action="{{ route('admin.furniture.categories.store') }}">
            @csrf
            <h2>Nueva categoría de mobiliario</h2>
            <label for="category-name">Nombre de la categoría</label>
            <input id="category-name" name="name" maxlength="100" required placeholder="Ej. Mobiliario erótico, descanso, iluminación">
            <button class="btn" type="submit">Crear categoría</button>
        </form>
        <form class="card" method="POST" action="{{ route('admin.furniture.items.store') }}">
            @csrf
            <h2>Nuevo elemento</h2>
            <label for="item-category">Categoría de mobiliario</label>
            <select id="item-category" name="furniture_category_id" required @disabled($categories->isEmpty())>
                @foreach ($categories as $category)<option value="{{ $category->id }}">{{ $category->name }}</option>@endforeach
            </select>
            <label for="item-name">Nombre del elemento</label>
            <input id="item-name" name="name" maxlength="100" required placeholder="Ej. Sillón tántrico">
            <label for="item-icon">Ícono</label>
            <select id="item-icon" name="icon"><option value="🛏️">🛏️ Cama</option><option value="🪑">🪑 Sillón</option><option value="🪞">🪞 Espejo</option><option value="🚿">🚿 Ducha</option><option value="📺">📺 TV</option><option value="📶">📶 Wi‑Fi</option><option value="🎲">🎲 Juegos</option><option value="💡">💡 Iluminación</option><option value="🔊">🔊 Audio</option><option value="🧴">🧴 Accesorios</option></select>
            <button class="btn" type="submit" @disabled($categories->isEmpty())>Crear elemento</button>
            @if ($categories->isEmpty())<p>Primero crea una categoría.</p>@endif
        </form>
    </div>
    <h2>Catálogo por categoría</h2>
    @forelse ($categories as $category)
        <section class="card">
            <h3>{{ $category->name }} <span class="pill">{{ $category->items->count() }} elementos</span>
                @if($category->items->isEmpty())
                    <form method="POST" action="{{ route('admin.furniture.categories.destroy', $category) }}" style="display:inline" onsubmit="return confirm('¿Eliminar esta categoría?')">@csrf @method('DELETE')<button class="btn secondary" type="submit">Eliminar categoría</button></form>
                @endif
            </h3>
            <details>
                <summary>Renombrar categoría</summary>
                <form method="POST" action="{{ route('admin.furniture.categories.update', $category) }}">
                    @csrf @method('PUT')
                    <label>Nombre <input name="name" value="{{ $category->name }}" maxlength="100" required></label>
                    <button class="btn" type="submit">Guardar categoría</button>
                </form>
            </details>
            @foreach ($category->items as $item)
                <details class="hh-equipment-edit">
                    <summary><span class="equipment-icon">{{ $item->icon ?? '✦' }}</span> {{ $item->name }} · Editar</summary>
                    <div style="margin:8px 0 12px;padding:10px 12px;background:#f5f5f5;border-radius:8px;color:#555;font-size:13px;">
                        <strong>Total registrado: {{ $item->rooms->sum(fn($room) => (int) $room->pivot->quantity) }}</strong>
                        @if($item->rooms->isNotEmpty())
                            <div style="margin-top:5px;">Asignado en:
                                @foreach($item->rooms as $room)
                                    <span style="display:inline-block;margin:3px 4px 0 0;padding:3px 7px;background:#e7e7e7;border-radius:5px;">{{ $room->name }} ×{{ $room->pivot->quantity }}</span>
                                @endforeach
                            </div>
                        @else
                            <div style="margin-top:5px;color:#888;">Sin habitaciones asignadas</div>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('admin.furniture.items.update', $item) }}">
                        @csrf @method('PUT')
                        <label>Nombre <input name="name" value="{{ $item->name }}" maxlength="100" required></label>
                        <label>Ícono <select name="icon">@foreach(['🛏️'=>'Cama','🪑'=>'Sillón','🪞'=>'Espejo','🚿'=>'Ducha','📺'=>'TV','📶'=>'Wi‑Fi','🎲'=>'Juegos','💡'=>'Iluminación','🔊'=>'Audio','🧴'=>'Accesorios'] as $icon => $label)<option value="{{ $icon }}" @selected(($item->icon ?? '✦') === $icon)>{{ $icon }} {{ $label }}</option>@endforeach</select></label>
                        <label>Categoría <select name="furniture_category_id">
                            @foreach ($categories as $option)<option value="{{ $option->id }}" @selected($option->id === $category->id)>{{ $option->name }}</option>@endforeach
                        </select></label>
                        <button class="btn" type="submit">Guardar elemento</button>
                    </form>
                    @if($item->rooms()->count() === 0)
                        <form method="POST" action="{{ route('admin.furniture.items.destroy', $item) }}" onsubmit="return confirm('¿Eliminar este elemento?')">@csrf @method('DELETE')<button class="btn secondary" type="submit">Eliminar elemento</button></form>
                    @else
                        <small>Está asignado a una habitación y no se puede eliminar.</small>
                    @endif
                </details>
            @endforeach
        </section>
    @empty
        <p class="hh-empty-equipment">Aún no hay categorías. Crea el catálogo con los elementos reales de HH Motel.</p>
    @endforelse
    <a class="btn secondary" href="{{ route('admin.rooms.index') }}">Ir a habitaciones</a>
@endsection
