@extends('admin.layout')
@section('title', 'Turnos')
@section('content')
    <style>
        .shift-nav { display:flex; align-items:center; gap:12px; margin-bottom:14px; flex-wrap:wrap; }
        .shift-nav a.navbtn { background:#1c1c1c; border:1px solid #333; color:#ccc; text-decoration:none; padding:8px 14px; border-radius:7px; font-size:13px; }
        .shift-nav a.navbtn:hover { border-color:#ff7918; color:#ff7918; }
        .shift-nav .today-pill { font-size:11.5px; color:#6fd39a; background:#1c3a2a; padding:3px 10px; border-radius:20px; }
        .shift-nav select { margin-left:auto; }

        .conflict-banner { background:#3a1c1c; border:1px solid #7a2d2d; color:#f3b8b8; padding:12px 16px; border-radius:9px; margin-bottom:18px; font-size:13.5px; }
        .conflict-banner ul { margin:6px 0 0; padding-left:18px; }

        .time-grid { position:relative; display:grid; grid-template-columns:46px repeat(7, 1fr); grid-auto-rows:6px; border:1px solid #333; background:#151515; border-radius:8px; overflow:hidden; margin-top:12px; }
        .tg-daylabel { grid-row:1; padding:7px 4px; text-align:center; font-size:10.5px; color:#999; text-transform:uppercase; background:#181818; border-left:1px solid #292929; position:sticky; top:0; z-index:5; }
        .tg-daylabel .dnum { display:block; font-size:13px; color:#eee; font-weight:700; }
        .tg-daylabel.today { color:#ff9a4a; background:#241a10; }
        .tg-corner { grid-row:1; grid-column:1; background:#181818; }
        .tg-hourlabel { grid-column:1; font-size:9px; color:#666; text-align:right; padding-right:6px; border-top:1px solid #232323; white-space:nowrap; }
        .tg-hourlabel-end { font-weight:700; border-top:2px dashed; padding-top:2px; }
        .tg-daybg { border-left:1px solid #232323; position:relative; }
        .tg-daybg.today { background:rgba(255,121,24,.05); }
        .shift-cell { position:relative; }
        .tg-boundary-line { grid-column:2 / -1; border-top:2px dashed; pointer-events:none; z-index:3; }
        .tg-boundary-label { grid-column:1; justify-self:end; align-self:start; transform:translateY(-50%); font-size:8.5px; font-weight:700; padding:1px 5px; border-radius:3px; white-space:nowrap; z-index:6; margin-right:2px; }
        .business-hours-legend { display:flex; gap:14px; flex-wrap:wrap; font-size:11.5px; color:#999; margin:6px 0 2px; }
        .business-hours-legend span.sw { display:inline-block; width:14px; border-top:2px dashed; margin-right:5px; vertical-align:middle; }
        details.shift-block-details summary { list-style:none; cursor:pointer; }
        details.shift-block-details summary::-webkit-details-marker { display:none; }
        .shift-block { position:absolute; inset:1px; border-radius:4px; padding:2px 4px; font-size:10px; font-weight:700; color:#111; overflow:hidden; line-height:1.15; box-shadow:0 1px 3px rgba(0,0,0,.4); }
        .shift-block .t { display:block; font-weight:500; font-size:8.5px; opacity:.8; }
        .shift-block.conflict { outline:2px solid #ff3b3b; outline-offset:-2px; }
        /* Modal centrado en vez de flotar pegado a la celda: en una grilla
           de 7 columnas angostas, un panel "al lado" del bloque termina
           tapando los turnos del día vecino -- confuso, sobre todo cerca
           del borde derecho de la semana. Centrado + fondo oscurecido deja
           claro que es un dialogo aparte, sin importar qué día se edite. */
        .edit-shift-backdrop { position:fixed; inset:0; background:rgba(0,0,0,.6); z-index:150; }
        .edit-shift-panel { position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); z-index:200; background:#181818; border:1px solid #3a3a3a; border-radius:10px; padding:14px; width:min(260px, 90vw); max-height:85vh; overflow-y:auto; box-shadow:0 16px 48px rgba(0,0,0,.6); }
        .edit-shift-panel form { display:flex; flex-direction:column; gap:6px; margin-bottom:6px; }
        .edit-shift-panel input, .edit-shift-panel select { font-size:12px; padding:5px 6px; }

        table.hours-table { width:100%; border-collapse:collapse; font-size:13px; }
        table.hours-table th, table.hours-table td { padding:8px 10px; text-align:left; border-bottom:1px solid #292929; }
        table.hours-table th { color:#999; font-weight:600; font-size:11px; text-transform:uppercase; }
        table.hours-table td.num { text-align:right; font-family: ui-monospace, monospace; }
        .extra-pill { display:inline-block; padding:2px 9px; border-radius:20px; font-size:11.5px; font-weight:700; }
        .extra-pill.none { background:#1c3a2a; color:#6fd39a; }
        .extra-pill.some { background:#3a1c22; color:#e88a9a; }
        .extra-pill.unknown { background:#2a2a2a; color:#888; }

        .add-shift-card { margin-top:14px; }
        .add-shift-card form { display:grid; grid-template-columns:repeat(5, 1fr) auto; gap:10px; align-items:end; }
        @media (max-width: 900px) { .add-shift-card form { grid-template-columns:1fr 1fr; } }

        .role-legend { display:flex; gap:10px; flex-wrap:wrap; margin:8px 0 4px; font-size:11.5px; }
        .role-legend span.dot { display:inline-block; width:9px; height:9px; border-radius:50%; margin-right:4px; vertical-align:middle; }
    </style>

    <h1>Turnos</h1>
    <p class="sub">Calendario semanal por rol -- cada bloque dibuja su horario real. <a class="link" href="{{ route('admin.staff.index') }}">Administrar personal →</a></p>
    <div class="business-hours-legend">
        <span><span class="sw" style="border-color:#6fd39a;"></span>10:30 apertura</span>
        <span><span class="sw" style="border-color:#f2994a;"></span>22:30 cierre fin de semana</span>
        <span><span class="sw" style="border-color:#e88a9a;"></span>03:00 cierre entre semana</span>
    </div>

    <div class="shift-nav">
        <a class="navbtn" href="{{ route('admin.shifts.index', array_filter(['date' => $prevWeek, 'staff_id' => $selectedStaffId])) }}">← Semana anterior</a>
        <strong>Semana del {{ $weekStart->locale('es')->isoFormat('D [de] MMMM') }} al {{ $weekEnd->locale('es')->isoFormat('D [de] MMMM') }}</strong>
        @if ($isCurrentWeek)<span class="today-pill">semana actual</span>@endif
        <a class="navbtn" href="{{ route('admin.shifts.index', array_filter(['date' => $nextWeek, 'staff_id' => $selectedStaffId])) }}">Semana siguiente →</a>

        <form method="GET" action="{{ route('admin.shifts.index') }}">
            <input type="hidden" name="date" value="{{ $weekStart->toDateString() }}">
            <select name="staff_id" onchange="this.form.submit()">
                <option value="">Todo el personal</option>
                @foreach ($staffByRole as $roleName => $people)
                    <optgroup label="{{ $roleName }}">
                        @foreach ($people as $person)
                            <option value="{{ $person->id }}" @selected($selectedStaffId === $person->id)>{{ $person->name }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </form>
    </div>

    @if ($hasConflicts)
        <div class="conflict-banner">
            <strong>⚠ Choque de horarios</strong> -- la misma persona tiene turnos que se superponen:
            <ul>
                @foreach ($conflictShifts as $shift)
                    <li>{{ $shift->staff->name }} -- {{ ucfirst($shift->date->locale('es')->isoFormat('dddd D/MM')) }}, {{ $shift->rangeLabel() }}{{ $shift->crossesMidnight() ? ' (+1 día)' : '' }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @forelse ($roleBlocks as $role => $blocks)
        <div class="card">
            <h2 style="text-transform:uppercase; font-size:14px; letter-spacing:.04em; color:#ff9a4a; margin-bottom:2px;">{{ $role }}</h2>
            <div class="role-legend">
                @foreach ($blocks->pluck('shift.staff')->unique('id') as $person)
                    <span><span class="dot" style="background:{{ $blocks->firstWhere('shift.staff_id', $person->id)['color'] }};"></span>{{ $person->name }}</span>
                @endforeach
            </div>
            <div style="overflow-x:auto;">
                <div class="time-grid" style="grid-template-rows: 40px repeat({{ $totalSlots }}, 6px) 16px; min-width:640px;">
                    <div class="tg-corner"></div>
                    @for ($i = 0; $i < 7; $i++)
                        @php $day = $weekStart->copy()->addDays($i); @endphp
                        <div class="tg-daylabel {{ $day->isToday() ? 'today' : '' }}" style="grid-column:{{ $i + 2 }};">
                            {{ ucfirst($day->locale('es')->isoFormat('ddd')) }}
                            <span class="dnum">{{ $day->format('d') }}</span>
                        </div>
                    @endfor

                    @foreach ($hourMarks as $mark)
                        <div class="tg-hourlabel" style="grid-row:{{ $mark['slot'] + 2 }} / span 4;">{{ $mark['label'] }}</div>
                    @endforeach
                    <div class="tg-hourlabel tg-hourlabel-end" style="grid-row:{{ $totalSlots + 2 }}; border-color:#e88a9a; color:#e88a9a;">{{ $closingLabel }}</div>

                    @for ($i = 0; $i < 7; $i++)
                        @php $day = $weekStart->copy()->addDays($i); @endphp
                        <div class="tg-daybg {{ $day->isToday() ? 'today' : '' }}" style="grid-column:{{ $i + 2 }}; grid-row:2 / span {{ $totalSlots }};"></div>
                    @endfor

                    @foreach ($businessMarks as $mark)
                        <div class="tg-boundary-line" style="grid-row:{{ $mark['slot'] + 2 }}; border-color:{{ $mark['color'] }};"></div>
                        <div class="tg-boundary-label" style="grid-row:{{ $mark['slot'] + 2 }}; background:{{ $mark['color'] }}; color:#111;" title="{{ $mark['desc'] }}">{{ $mark['label'] }}</div>
                    @endforeach

                    @foreach ($blocks as $block)
                        @php $shift = $block['shift']; @endphp
                        <div class="shift-cell" style="grid-column:{{ $block['day'] + 2 }}; grid-row:{{ $block['start_slot'] + 2 }} / span {{ $block['span'] }};">
                            <details class="shift-block-details">
                                <summary class="shift-block {{ $block['conflict'] ? 'conflict' : '' }}" style="background:{{ $block['color'] }};" title="{{ $shift->staff->name }} · {{ $shift->rangeLabel() }}{{ $shift->notes ? ' · '.$shift->notes : '' }}">
                                    {{ $shift->staff->name }}
                                    <span class="t">{{ $shift->rangeLabel() }}</span>
                                </summary>
                                <div class="edit-shift-backdrop" onclick="this.closest('details').removeAttribute('open')"></div>
                                <div class="edit-shift-panel">
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
                                        <input type="text" name="notes" value="{{ $shift->notes }}" placeholder="Notas">
                                        <button class="btn secondary" type="submit" style="padding:6px; font-size:11.5px;">Guardar</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}" onsubmit="return confirm('¿Eliminar este turno?');">
                                        @csrf @method('DELETE')
                                        <button class="btn secondary" type="submit" style="padding:6px; font-size:11.5px; width:100%;">Eliminar</button>
                                    </form>
                                </div>
                            </details>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @empty
        <p class="sub">Sin turnos cargados esta semana todavía.</p>
    @endforelse

    <div class="card">
        <h2 style="font-size:14px;">Horas de la semana</h2>
        <p class="sub" style="margin:2px 0 10px;">Horas asignadas contra las horas legales de cada persona -- lo que pasa de ahí es hora extra.</p>
        @if ($hoursSummary->isEmpty())
            <p class="sub">Sin turnos esta semana.</p>
        @else
            <table class="hours-table">
                <thead><tr><th>Persona</th><th>Rol</th><th style="text-align:right;">Asignadas</th><th style="text-align:right;">Legales</th><th>Extra</th></tr></thead>
                <tbody>
                    @foreach ($hoursSummary as $row)
                        <tr>
                            <td>{{ $row['staff']->name }}</td>
                            <td>{{ $row['staff']->role }}</td>
                            <td class="num">{{ $row['assigned'] }} h</td>
                            <td class="num">{{ $row['legal'] !== null ? $row['legal'].' h' : '—' }}</td>
                            <td>
                                @if ($row['extra'] === null)
                                    <span class="extra-pill unknown">sin dato</span>
                                @elseif ($row['extra'] > 0)
                                    <span class="extra-pill some">+{{ $row['extra'] }} h extra</span>
                                @else
                                    <span class="extra-pill none">sin extra</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="card">
        <h2 style="font-size:14px;">Resumen mensual -- {{ $monthLabel }}</h2>
        <p class="sub" style="margin:2px 0 10px;">Horas legales prorrateadas por los días del mes (horas semanales ÷ 7 × días del mes).</p>
        @if ($monthlySummary->isEmpty())
            <p class="sub">Sin turnos este mes.</p>
        @else
            <table class="hours-table">
                <thead><tr><th>Persona</th><th>Rol</th><th style="text-align:right;">Asignadas</th><th style="text-align:right;">Legales (aprox.)</th><th>Extra</th></tr></thead>
                <tbody>
                    @foreach ($monthlySummary as $row)
                        <tr>
                            <td>{{ $row['staff']->name }}</td>
                            <td>{{ $row['staff']->role }}</td>
                            <td class="num">{{ $row['assigned'] }} h</td>
                            <td class="num">{{ $row['legal'] !== null ? $row['legal'].' h' : '—' }}</td>
                            <td>
                                @if ($row['extra'] === null)
                                    <span class="extra-pill unknown">sin dato</span>
                                @elseif ($row['extra'] > 0)
                                    <span class="extra-pill some">+{{ $row['extra'] }} h extra</span>
                                @else
                                    <span class="extra-pill none">sin extra</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

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
