@extends('admin.layout')
@section('title', $offer->exists ? 'Editar oferta' : 'Nueva oferta')
@section('content')
    <style>
        .scope-grid { display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:6px; margin-top:6px; }
        .scope-grid label { display:flex; align-items:center; gap:7px; font-size:12.5px; background:#161616; border:1px solid #2c2c2c; border-radius:7px; padding:7px 9px; margin:0; cursor:pointer; }
        .scope-grid label:has(input:checked) { border-color:#b088e8; background:#241f2e; }
        .scope-grid input { width:auto; }
        .cat-block { margin-bottom:8px; }
        .cat-block > .cat-name { font-size:11px; text-transform:uppercase; letter-spacing:.04em; color:#888; margin:12px 0 4px; }
        .days-row { display:flex; gap:10px; flex-wrap:wrap; margin-top:6px; }
        .days-row label { display:flex; align-items:center; gap:6px; font-size:13px; margin:0; }
        .days-row input { width:auto; }
    </style>

    @php
        $days = [1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue', 5 => 'Vie', 6 => 'Sáb', 0 => 'Dom'];
        $selDays = old('allowed_weekdays', $offer->allowed_weekdays ?? []);
        $selRooms = collect(old('room_ids', $offer->exists ? $offer->rooms->pluck('id')->all() : []))->map(fn ($v) => (int) $v);
        $selCats = collect(old('category_ids', $offer->exists ? $offer->roomCategories->pluck('id')->all() : []))->map(fn ($v) => (int) $v);
        $dt = old('discount_type', $offer->discount_type ?? 'percentage');
    @endphp

    <h1>{{ $offer->exists ? 'Editar oferta' : 'Nueva oferta' }}</h1>
    <p class="sub">Se aplica sola al reservar, sin código.</p>

    <form method="POST" action="{{ $offer->exists ? route('admin.offers.update', $offer) : route('admin.offers.store') }}">
        @csrf
        @if ($offer->exists) @method('PUT') @endif

        <label>Nombre de la oferta</label>
        <input type="text" name="internal_name" value="{{ old('internal_name', $offer->internal_name) }}" placeholder="Ej. Oferta finde PLUS · Semana de aniversario" required>

        <div class="row2">
            <div>
                <label>Tipo</label>
                <select name="discount_type" id="dt-select" onchange="hhDtHint()">
                    <option value="percentage" @selected($dt === 'percentage')>Porcentaje de descuento</option>
                    <option value="precio_fijo" @selected($dt === 'precio_fijo')>Precio fijo de oferta</option>
                </select>
            </div>
            <div>
                <label id="dv-label">Valor</label>
                <input type="number" name="discount_value" min="1" value="{{ old('discount_value', $offer->discount_value) }}" required>
                <div class="hint" id="dv-hint"></div>
            </div>
        </div>

        <div class="row2">
            <div>
                <label>Vigencia desde</label>
                <input type="date" name="starts_at" value="{{ old('starts_at', optional($offer->starts_at)->toDateString()) }}">
            </div>
            <div>
                <label>Vigencia hasta</label>
                <input type="date" name="ends_at" value="{{ old('ends_at', optional($offer->ends_at)->toDateString()) }}">
            </div>
        </div>

        <label>Días de la semana <span class="hint" style="margin:0;">(vacío = todos)</span></label>
        <div class="days-row">
            @foreach ($days as $value => $label)
                <label><input type="checkbox" name="allowed_weekdays[]" value="{{ $value }}" @checked(in_array($value, $selDays)) style="width:auto;"> {{ $label }}</label>
            @endforeach
        </div>

        <div class="row2">
            <div>
                <label>Horario desde (opcional)</label>
                <input type="time" name="allowed_time_start" value="{{ old('allowed_time_start', $offer->allowed_time_start) }}">
            </div>
            <div>
                <label>Horario hasta (opcional)</label>
                <input type="time" name="allowed_time_end" value="{{ old('allowed_time_end', $offer->allowed_time_end) }}">
            </div>
        </div>

        <label style="margin-top:18px;">¿A qué aplica? — categorías completas y/o habitaciones puntuales</label>

        <div class="cat-name" style="margin-top:6px;">Categorías completas</div>
        <div class="scope-grid">
            @foreach ($categories as $cat)
                <label><input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" @checked($selCats->contains($cat->id))> {{ $cat->name }}</label>
            @endforeach
        </div>

        @foreach ($rooms->groupBy('category.name') as $catName => $catRooms)
            <div class="cat-block">
                <div class="cat-name">{{ $catName }} — habitaciones puntuales</div>
                <div class="scope-grid">
                    @foreach ($catRooms as $room)
                        <label><input type="checkbox" name="room_ids[]" value="{{ $room->id }}" @checked($selRooms->contains($room->id))> {{ $room->name }}</label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <label style="margin-top:18px;">Estado</label>
        <select name="is_active">
            <option value="1" @selected(old('is_active', $offer->is_active ?? true))>Activa</option>
            <option value="0" @selected(! old('is_active', $offer->is_active ?? true))>Pausada</option>
        </select>

        <div class="actions" style="margin-top:20px;">
            <button class="btn" type="submit">Guardar oferta</button>
            <a class="link" href="{{ route('admin.offers.index') }}">Cancelar</a>
        </div>
    </form>

    <script>
        function hhDtHint() {
            const dt = document.getElementById('dt-select').value;
            const label = document.getElementById('dv-label');
            const hint = document.getElementById('dv-hint');
            if (dt === 'percentage') {
                label.textContent = 'Porcentaje (%)';
                hint.textContent = 'Ej. 20 = 20% de descuento sobre el precio normal.';
            } else {
                label.textContent = 'Precio fijo de oferta (CLP)';
                hint.textContent = 'Ej. 28000 = la habitación queda a $28.000 sin importar su precio normal.';
            }
        }
        hhDtHint();
    </script>
@endsection
