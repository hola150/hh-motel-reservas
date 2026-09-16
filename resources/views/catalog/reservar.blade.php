<!doctype html>
<html lang="es" style="background:#111;color:#eee">
<head>
    <meta charset="utf-8">
    <meta name="color-scheme" content="dark">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservar — HH Motel</title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body { background:#111; color:#eee; font-family: -apple-system, "Segoe UI", sans-serif; margin: 0; line-height: 1.45; }
        .wrap { max-width: 480px; margin: 0 auto; padding: 32px 20px 60px; }
        a.back { color:#999; text-decoration:none; font-size:13px; display:inline-block; margin-bottom: 18px; }
        .brand { font-size: 13px; font-weight: 800; letter-spacing: .1em; color: #ff7918; margin-bottom: 8px; }
        h1 { font-size: 22px; margin: 0 0 6px; }
        p.sub { color:#999; font-size: 13.5px; margin: 0 0 24px; }
        .hint-box { background:#1c2f1c; border:1px solid #2e5a2e; color:#8fe0ad; border-radius:9px; padding:12px 14px; font-size:12.5px; margin-bottom:22px; }
        .errors { background:#3a1c1c; border:1px solid #7a2d2d; color:#f3b8b8; padding:12px 14px; border-radius:8px; margin-bottom: 18px; font-size:14px; }
        label { display:block; font-size: 12.5px; color:#bbb; margin: 16px 0 6px; }
        input, select { width:100%; box-sizing:border-box; background:#1c1c1c; border:1px solid #333; color:#fff; padding:11px 12px; border-radius:8px; font-size:15px; }
        .row2 { display:grid; grid-template-columns: 1fr 1fr; gap:12px; }
        .honey-field { position:absolute; left:-9999px; top:-9999px; }
        button.submit { width:100%; margin-top:26px; background:#ff7918; color:#fff; border:none; padding:14px; border-radius:9px; font-size:15px; font-weight:700; cursor:pointer; }
        button.submit:hover { background:#df6209; }
        .legal { font-size:11.5px; color:#777; margin-top:14px; text-align:center; }
    </style>
</head>
<body>
    <div class="wrap">
        <a class="back" href="{{ route('catalog.index') }}">← Volver al catálogo</a>
        <div class="brand">HH MOTEL</div>
        <h1>Reservá tu habitación</h1>
        <p class="sub">Confirmación inmediata según disponibilidad. El documento de identidad se verifica al llegar.</p>

        <div class="hint-box">Horario: lunes a jueves 10:30 a 03:00 · viernes a domingo 10:30 a 22:30 corrido.</div>

        @if ($errors->any())
            <div class="errors">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('catalog.reserve.store') }}" id="reserve-form">
            @csrf
            <div class="honey-field" aria-hidden="true">
                <label for="website">No completar</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <label for="room_category_id">Tipo de habitación</label>
            <select id="room_category_id" name="room_category_id" required onchange="hhUpdateDurations()">
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(old('room_category_id', $selectedCategoryId) == $cat->id)>{{ $cat->name }}</option>
                @endforeach
            </select>

            <div class="row2">
                <div>
                    <label for="date">Fecha</label>
                    <input type="date" id="date" name="date" min="{{ now()->toDateString() }}" value="{{ old('date', now()->toDateString()) }}" required>
                </div>
                <div>
                    <label for="time">Hora</label>
                    <input type="time" id="time" required onchange="hhSyncTime()" value="{{ old('time_hour') !== null ? sprintf('%02d:%02d', old('time_hour'), old('time_minute')) : '' }}">
                    <input type="hidden" name="time_hour" id="time_hour" value="{{ old('time_hour') }}">
                    <input type="hidden" name="time_minute" id="time_minute" value="{{ old('time_minute') }}">
                </div>
            </div>

            <label for="duration_minutes">Duración</label>
            <select id="duration_minutes" name="duration_minutes" required></select>

            <label for="guests_count">Cantidad de personas</label>
            <input type="number" id="guests_count" name="guests_count" min="1" max="10" value="{{ old('guests_count', 2) }}" required>

            <div class="row2">
                <div>
                    <label for="first_name">Nombre</label>
                    <input type="text" id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                </div>
                <div>
                    <label for="last_name">Apellido</label>
                    <input type="text" id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                </div>
            </div>

            <label for="phone">Teléfono / WhatsApp</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+56 9 1234 5678" required>

            <label for="email">Email (opcional)</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">

            <button class="submit" type="submit">Confirmar reserva</button>
            <p class="legal">Al reservar aceptás presentar tu documento de identidad al llegar. HH Motel se reserva el derecho de admisión.</p>
        </form>
    </div>
    <script>
        const hhDurationsByCategory = @json($durationsByCategory->mapWithKeys(fn ($v, $k) => [(string) $k => $v]));
        const hhDurationLabel = (min) => min >= 60 ? (min / 60) + ' h' : min + ' min';

        function hhUpdateDurations() {
            const catId = document.getElementById('room_category_id').value;
            const select = document.getElementById('duration_minutes');
            const options = hhDurationsByCategory[catId] || [];
            const prevValue = '{{ old('duration_minutes') }}';
            select.innerHTML = '';
            options.forEach((min) => {
                const opt = document.createElement('option');
                opt.value = min;
                opt.textContent = hhDurationLabel(min);
                if (String(min) === prevValue) opt.selected = true;
                select.appendChild(opt);
            });
        }

        function hhSyncTime() {
            const [h, m] = document.getElementById('time').value.split(':');
            document.getElementById('time_hour').value = h;
            document.getElementById('time_minute').value = m;
        }

        document.getElementById('reserve-form').addEventListener('submit', hhSyncTime);
        hhUpdateDurations();
    </script>
</body>
</html>
