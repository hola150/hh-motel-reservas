@extends('admin.layout')
@section('title', 'Mobiliario')
@section('content')
    <h1>Mobiliario y equipamiento</h1>
    <p class="sub">Organiza el catálogo por categorías. Asigna después los elementos a cada habitación desde su ficha.</p>
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
            <h3>{{ $category->name }} <span class="pill">{{ $category->items->count() }} elementos</span></h3>
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
                    <form method="POST" action="{{ route('admin.furniture.items.update', $item) }}">
                        @csrf @method('PUT')
                        <label>Nombre <input name="name" value="{{ $item->name }}" maxlength="100" required></label>
                        <label>Ícono <select name="icon">@foreach(['🛏️'=>'Cama','🪑'=>'Sillón','🪞'=>'Espejo','🚿'=>'Ducha','📺'=>'TV','📶'=>'Wi‑Fi','🎲'=>'Juegos','💡'=>'Iluminación','🔊'=>'Audio','🧴'=>'Accesorios'] as $icon => $label)<option value="{{ $icon }}" @selected(($item->icon ?? '✦') === $icon)>{{ $icon }} {{ $label }}</option>@endforeach</select></label>
                        <label>Categoría <select name="furniture_category_id">
                            @foreach ($categories as $option)<option value="{{ $option->id }}" @selected($option->id === $category->id)>{{ $option->name }}</option>@endforeach
                        </select></label>
                        <button class="btn" type="submit">Guardar elemento</button>
                    </form>
                </details>
            @endforeach
        </section>
    @empty
        <p class="hh-empty-equipment">Aún no hay categorías. Crea el catálogo con los elementos reales de HH Motel.</p>
    @endforelse
    <a class="btn secondary" href="{{ route('admin.rooms.index') }}">Ir a habitaciones</a>
@endsection
