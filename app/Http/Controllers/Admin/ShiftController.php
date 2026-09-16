<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Panel semanal de turnos, agrupado por rol -- una tabla por rol
     * (Anfitrión, Mucama, ...), una fila por cada franja horaria distinta
     * usada esa semana, una columna por día. Replica el formato de la
     * planilla que ya usaba recepción, pero editable.
     */
    public function index(Request $request): View
    {
        $anchor = $request->query('date') ? Carbon::parse($request->query('date')) : now('America/Santiago');
        $weekStart = $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

        $shifts = Shift::with('staff')
            ->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->sortBy(fn (Shift $s) => $s->start_time);

        // Una tabla por rol -- dentro de cada una, una fila por franja
        // horaria distinta (agrupa los turnos que comparten el mismo
        // horario aunque sean de días distintos), ordenadas por hora de
        // inicio, con una celda por día de la semana.
        $roleTables = $shifts->groupBy(fn (Shift $s) => $s->staff->role)
            ->map(function ($roleShifts) use ($weekStart) {
                return $roleShifts->groupBy(fn (Shift $s) => $s->rangeLabel())
                    ->map(function ($rangeShifts) use ($weekStart) {
                        $cells = [];
                        foreach ($rangeShifts as $shift) {
                            $dayIndex = $weekStart->diffInDays($shift->date->copy()->startOfDay());
                            $cells[$dayIndex][] = $shift;
                        }

                        return [
                            'range' => $rangeShifts->first()->rangeLabel(),
                            'start_time' => $rangeShifts->first()->start_time,
                            'cells' => $cells,
                        ];
                    })
                    ->sortBy('start_time')
                    ->values();
            });

        // Horas asignadas esta semana por persona, contra sus horas legales
        // -- para ver de un vistazo quién quedó con horas extra. Solo se
        // calcula para quien tiene horas legales cargadas.
        $hoursSummary = $shifts->groupBy('staff_id')
            ->map(function ($personShifts) {
                $staff = $personShifts->first()->staff;
                $assigned = round($personShifts->sum(fn (Shift $s) => $s->durationHours()), 1);
                $legal = $staff->legal_hours_per_week;

                return [
                    'staff' => $staff,
                    'assigned' => $assigned,
                    'legal' => $legal,
                    'extra' => $legal !== null ? round(max(0, $assigned - $legal), 1) : null,
                ];
            })
            ->sortBy(fn (array $row) => $row['staff']->role.$row['staff']->name)
            ->values();

        return view('admin.shifts.index', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'roleTables' => $roleTables,
            'hoursSummary' => $hoursSummary,
            'staffByRole' => Staff::where('is_active', true)->orderBy('role')->orderBy('name')->get()->groupBy('role'),
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'isCurrentWeek' => now('America/Santiago')->between($weekStart, $weekEnd),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $shift = Shift::create($validated);
        AuditLog::record(auth()->id(), 'turno.crear', 'Shift', $shift->id, null, $shift->toArray());

        return redirect()->route('admin.shifts.index', ['date' => $validated['date']])->with('status', 'Turno agregado.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'exists:staff,id'],
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $old = $shift->toArray();
        $shift->update($validated);
        AuditLog::record(auth()->id(), 'turno.editar', 'Shift', $shift->id, $old, $shift->toArray());

        return redirect()->route('admin.shifts.index', ['date' => $validated['date']])->with('status', 'Turno actualizado.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $date = $shift->date->toDateString();
        AuditLog::record(auth()->id(), 'turno.eliminar', 'Shift', $shift->id, $shift->toArray(), null);
        $shift->delete();

        return redirect()->route('admin.shifts.index', ['date' => $date])->with('status', 'Turno eliminado.');
    }
}
