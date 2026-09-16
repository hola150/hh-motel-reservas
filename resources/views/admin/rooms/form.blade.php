@extends('admin.layout')
@section('title', $room->exists ? 'Editar Playroom' : 'Nuevo Playroom')
@section('content')
    <h1>{{ $room->exists ? 'Editar Playroom' : 'Nuevo Playroom' }}</h1>
    <p class="sub">{{ $room->exists ? $room->name : 'Registrar un Playroom real' }}</p>

    <form method="POST" action="{{ $room->exists ? route('admin.rooms.update', $room) : route('admin.rooms.store') }}">
        @csrf
        @if ($room->exists) @method('PUT') @endif

        <div class="row2">
            <div>
                <label>Nombre / número</label>
                <input type="text" name="name" value="{{ old('name', $room->name) }}" placeholder="Ej. PLUS 206" required>
            </div>
            <div>
                <label>Categoría</label>
                <select name="room_category_id" required>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('room_category_id', $room->room_category_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row2">
            <div>
                <label>Buffer de aseo (minutos)</label>
                <input type="number" name="buffer_minutes" min="0" value="{{ old('buffer_minutes', $room->buffer_minutes ?? 15) }}" required>
            </div>
            <div>
                <label>Estado operativo</label>
                <select name="operational_status">
                    <option value="activa" @selected(old('operational_status', $room->operational_status ?? 'activa') === 'activa')>Activa</option>
                    <option value="mantencion" @selected(old('operational_status', $room->operational_status ?? '') === 'mantencion')>En mantención</option>
                    <option value="inactiva" @selected(old('operational_status', $room->operational_status ?? '') === 'inactiva')>Inactiva</option>
                    <option value="aseo" @selected(old('operational_status', $room->operational_status ?? '') === 'aseo')>En aseo</option>
                </select>
            </div>
        </div>

        <label>Observación operativa (opcional)</label>
        <input type="text" name="operational_note" value="{{ old('operational_note', $room->operational_note) }}" placeholder="Ej. Aire acondicionado en reparación">

        <section class="hh-furniture-section" aria-labelledby="furniture-heading">
            <h2 id="furniture-heading">Mobiliario de este Playroom</h2>
            <p class="sub">Indica cantidad y estado de cada elemento. Cantidad 0 significa que no está asignado a este Playroom.</p>
            <a class="link" href="{{ route('admin.furniture.index') }}" target="_blank" rel="noopener">Administrar categorías y elementos ↗</a>
            <input type="hidden" name="furniture_present" value="1">
            @forelse ($furnitureCategories as $furnitureCategory)
                <fieldset class="hh-equipment-group">
                    <legend>{{ $furnitureCategory->name }}</legend>
                    @forelse ($furnitureCategory->items as $item)
                        @php $assigned = $room->furniture->firstWhere('id', $item->id)?->pivot; @endphp
                        <div class="hh-equipment-row">
                            <strong><span class="equipment-icon">{{ $item->icon ?? '✦' }}</span> {{ $item->name }}</strong>
                            <input type="hidden" name="furniture[{{ $item->id }}][id]" value="{{ $item->id }}">
                            <label>Cantidad
                                <input aria-label="Cantidad de {{ $item->name }}" type="number" name="furniture[{{ $item->id }}][quantity]" min="0" max="100" value="{{ old('furniture.'.$item->id.'.quantity', $assigned?->quantity ?? 0) }}" required>
                            </label>
                            <label>Estado
                                <select aria-label="Estado de {{ $item->name }}" name="furniture[{{ $item->id }}][condition]">
                                    @foreach (['operativo' => 'Operativo', 'reparacion' => 'En reparación', 'fuera_de_uso' => 'Fuera de uso'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('furniture.'.$item->id.'.condition', $assigned?->condition ?? 'operativo') === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Observación
                                <input aria-label="Observación de {{ $item->name }}" name="furniture[{{ $item->id }}][notes]" maxlength="255" value="{{ old('furniture.'.$item->id.'.notes', $assigned?->notes) }}" placeholder="Opcional">
                            </label>
                        </div>
                    @empty
                        <p>Esta categoría todavía no tiene elementos.</p>
                    @endforelse
                </fieldset>
            @empty
                <div class="hh-empty-equipment">Primero crea las categorías y sus elementos en Mobiliario. Luego podrás asignarlos aquí a cada Playroom.</div>
            @endforelse
            <p class="sub">El estado del mobiliario es informativo: no cambia automáticamente el estado operativo del Playroom.</p>
        </section>
        <div class="actions" style="margin-top:20px;">
            <button class="btn" type="submit">Guardar</button>
            <a class="link" href="{{ route('admin.rooms.index') }}">Cancelar</a>
        </div>
    </form>
@endsection
