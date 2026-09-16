@extends('admin.layout')
@section('title', 'Turnos')
@section('content')
    <style>
        .shift-nav { display:flex; align-items:center; gap:12px; margin-bottom:18px; flex-wrap:wrap; }
        .shift-nav a.navbtn { background:#1c1c1c; border:1px solid #333; color:#ccc; text-decoration:none; padding:8px 14px; border-radius:7px; font-size:13px; }
        .shift-nav a.navbtn:hover { border-color:#ff7918; color:#ff7918; }
        .shift-nav .today-pill { font-size:11.5px; color:#6fd39a; background:#1c3a2a; padding:3px 10px; border-radius:20px; }
        table.shift-table { width:100%; border-collapse:collapse; font-size:13px; margin-top:12px; }
        table.shift-table th, table.shift-table td { border:1px solid #333; padding:7px 9px; text-align:center; vertical-align:top; }
        table.shift-table th { color:#999; font-weight:600; font-size:11.5px; text-transform:uppercase; background:#181818; }
        table.shift-table th .daydate { display:block; font-size:14px; color:#eee; font-weight:700; }
        table.shift-table td:first-child, table.shift-table th:first-child { text-align:left; white-space:nowrap; background:#181818; color:#ccc; font-weight:600; }
        .today-col { background:#1a2620 !important; }
        .shift-name { display:block; background:#23282e; border:1px solid #3a4048; border-radius:6px; padding:4px 7px; margin-bottom:3px; cursor:pointer; color:#eee; font-size:12.5px; }
        .shift-name:hover { border-color:#ff7918; }
        .shift-name .time { display:block; color:#999; font-size:10.5px; }
        .shift-name.has-notes { border-color:#7a6a1e; background:#2a2410; }
        details.edit-shift { margin-top:4px; text-align:left; }
        details.edit-shift form { display:flex; flex-direction:column; gap:5px; background:#111; border:1px solid #333; border-radius:6px; padding:8px; margin-top:4px; }
        details.edit-shift input, details.edit-shift select { font-size:12px; padding:4px 6px; }
        .add-shift-card { margin-top:14px; }
        .add-shift-card form { display:grid; grid-template-columns:repeat(5, 1fr) auto; gap:10px; align-items:end; }
        @media (max-width: 900px) { .add-shift-card form { grid-template-columns:1fr 1fr; } }
    </style>

    <h1>Turnos</h1>
    <p class="sub">Panel semanal por rol -- una fila por franja horaria, una columna por día. <a class="link" href="{{ route('admin.staff.index') }}">Administrar personal →</a></p>

    <div class="shift-nav">
        <a class="navbtn" href="{{ route('admin.shifts.index', ['date' => $prevWeek]) }}">← Semana anterior</a>
        <strong>Semana del {{ $weekStart->locale('es')->isoFormat('D [de] MMMM') }} al {{ $weekEnd->locale('es')->isoFormat('D [de] MMMM') }}</strong>
        @if ($isCurrentWeek)<span class="today-pill">semana actual</span>@endif
        <a class="navbtn" href="{{ route('admin.shifts.index', ['date' => $nextWeek]) }}">Semana siguiente →</a>
    </div>

    @forelse ($roleTables as $role => $rows)
        <div class="card">
            <h2 style="text-transform:uppercase; font-size:14px; letter-spacing:.04em; color:#ff9a4a;">{{ $role }}</h2>
            <div style="overflow-x:auto;">
                <table class="shift-table">
                    <thead>
                        <tr>
                            <th>Turno</th>
                            @for ($i = 0; $i < 7; $i++)
                                @php $day = $weekStart->copy()->addDays($i); @endphp
                                <th class="{{ $day->isToday() ? 'today-col' : '' }}">
                                    {{ ucfirst($day->locale('es')->isoFormat('dddd')) }}
                                    <span class="daydate">{{ $day->format('d') }}</span>
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $row)
                            <tr>
                                <td>TURNO {{ $row['range'] }}</td>
                                @for ($i = 0; $i < 7; $i++)
                                    <td class="{{ $weekStart->copy()->addDays($i)->isToday() ? 'today-col' : '' }}">
                                        @foreach (($row['cells'][$i] ?? []) as $shift)
                                            <details>
                                                <summary class="shift-name {{ $shift->notes ? 'has-notes' : '' }}" title="{{ $shift->notes }}">
                                                    {{ $shift->staff->name }}
                                                    @if ($shift->notes)<span class="time">{{ $shift->notes }}</span>@endif
                                                </summary>
                                                <details class="edit-shift">
                                                    <summary style="font-size:11px; color:#888; cursor:pointer;">Editar / eliminar</summary>
                                                    <form method="POST" action="{{ route('admin.shifts.update', $shift) }}">
                                                        @csrf @method('PUT')
                                                        <select name="staff_id" required>
                                                            @foreach ($staffByRole as $roleName => $people)
                                                                <optgroup label="{{ $roleName }}">
                                                                    @foreach ($people as $person)
                                                                        <option value="{{ $person->id }}" @selected($person->id === $shift->staff_id)>{{ $person->name }}</option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @endforeach
                                                        </select>
                                                        <input type="date" name="date" value="{{ $shift->date->toDateString() }}" required>
                                                        <input type="time" name="start_time" value="{{ $shift->startLabel() }}" required>
                                                        <input type="time" name="end_time" value="{{ $shift->endLabel() }}" required>
                                                        <input type="text" name="notes" value="{{ $shift->notes }}" placeholder="Notas (ej. reemplazo, horas hostel)">
                                                        <button class="btn secondary" type="submit" style="padding:6px; font-size:11.5px;">Guardar</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}" onsubmit="return confirm('¿Eliminar este turno?');" style="margin-top:4px;">
                                                        @csrf @method('DELETE')
                                                        <button class="btn secondary" type="submit" style="padding:6px; font-size:11.5px; width:100%;">Eliminar turno</button>
                                                    </form>
                                                </details>
                                            </details>
                                        @endforeach
                                    </td>
                                @endfor
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <p class="sub">Sin turnos cargados esta semana todavía.</p>
    @endforelse

    @if ($staffByRole->isEmpty())
        <p class="sub">Primero <a class="link" href="{{ route('admin.staff.index') }}">agregá personal</a> para poder cargar turnos.</p>
    @else
        <div class="card add-shift-card">
            <h2>Agregar turno</h2>
            <form method="POST" action="{{ route('admin.shifts.store') }}">
                @csrf
                <div>
                    <label>Persona</label>
                    <select name="staff_id" required>
                        <option value="" disabled selected>Elegir…</option>
                        @foreach ($staffByRole as $roleName => $people)
                            <optgroup label="{{ $roleName }}">
                                @foreach ($people as $person)
                                    <option value="{{ $person->id }}">{{ $person->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Día</label>
                    <select name="date" required>
                        @for ($i = 0; $i < 7; $i++)
                            @php $day = $weekStart->copy()->addDays($i); @endphp
                            <option value="{{ $day->toDateString() }}">{{ ucfirst($day->locale('es')->isoFormat('dddd D/MM')) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label>Inicio</label>
                    <input type="time" name="start_time" required>
                </div>
                <div>
                    <label>Fin</label>
                    <input type="time" name="end_time" required>
                </div>
                <div>
                    <label>Notas (opcional)</label>
                    <input type="text" name="notes" maxlength="255" placeholder="Ej. reemplazo de...">
                </div>
                <button class="btn" type="submit">Agregar</button>
            </form>
        </div>
    @endif
@endsection
