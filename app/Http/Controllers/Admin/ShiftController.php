<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Shift;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ShiftController extends Controller
{
    /**
     * Eje horario del calendario -- de 06:00 a 03:00 del día siguiente
     * (21 horas), en bloques de 15 min. Cubre con margen tanto el horario
     * HH (10:30 a 03:00) como el HOT (continuo fin de semana), más la
     * llegada temprana de mucamas/anfitriones antes de abrir.
     */
    private const GRID_START_HOUR = 6;

    private const GRID_TOTAL_SLOTS = 84; // 21 horas x 4 bloques de 15 min

    private const SLOT_MINUTES = 15;

    private const PALETTE = ['#5b9dd9', '#4ecdc4', '#b088e8', '#f2994a', '#e8c76f', '#6fd39a', '#e88a9a', '#7fbcdc', '#c9a6f5', '#f7b06a', '#9ad1d4', '#e69ec2'];

    /**
     * Panel semanal de turnos: un calendario por rol, con los turnos
     * dibujados como bloques según su horario real (no solo listados en
     * una tabla) -- así se ve de un vistazo cuánto rango cubre cada uno,
     * los huecos, y los choques de horario.
     */
    public function index(Request $request): View
    {
        $anchor = $request->query('date') ? Carbon::parse($request->query('date')) : now('America/Santiago');
        $weekStart = $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();
        $staffId = $request->query('staff_id') ? (int) $request->query('staff_id') : null;

        $shiftsQuery = Shift::with('staff')->whereBetween('date', [$weekStart->toDateString(), $weekEnd->toDateString()]);
        if ($staffId) {
            $shiftsQuery->where('staff_id', $staffId);
        }
        $shifts = $shiftsQuery->get();

        $conflictIds = $this->detectConflicts($shifts);

        $roleBlocks = $shifts->groupBy(fn (Shift $s) => $s->staff->role)
            ->map(function (Collection $roleShifts) use ($weekStart, $conflictIds) {
                return $roleShifts->map(function (Shift $shift) use ($weekStart, $conflictIds) {
                    [$startSlot, $span] = $this->gridPosition($shift);

                    return [
                        'shift' => $shift,
                        'day' => $weekStart->diffInDays($shift->date->copy()->startOfDay()),
                        'start_slot' => $startSlot,
                        'span' => $span,
                        'color' => self::PALETTE[$shift->staff_id % count(self::PALETTE)],
                        'conflict' => $conflictIds->contains($shift->id),
                    ];
                })->values();
            });

        // Marcas de hora en el eje (cada 1h = 4 bloques de 15 min). El eje va
        // de 06:00 a 03:00 del día siguiente, así que el último borde (03:00)
        // queda justo al fondo de la grilla y se muestra aparte como cierre.
        $hourMarks = collect(range(0, self::GRID_TOTAL_SLOTS / 4 - 1))->map(fn ($i) => [
            'slot' => $i * 4,
            'label' => str_pad((self::GRID_START_HOUR + $i) % 24, 2, '0', STR_PAD_LEFT).':00',
        ]);
        $closingLabel = str_pad((self::GRID_START_HOUR + intdiv(self::GRID_TOTAL_SLOTS, 4)) % 24, 2, '0', STR_PAD_LEFT).':00';

        // Horarios reales de apertura/cierre del motel, para dibujarlos como
        // líneas de referencia sobre la grilla (10:30 apertura todos los
        // días, 03:00 cierre entre semana, 22:30 cierre fin de semana/HOT).
        $businessMarks = [
            ['label' => '10:30', 'desc' => 'Apertura', 'slot' => $this->slotOffset(10, 30), 'color' => '#6fd39a'],
            ['label' => '22:30', 'desc' => 'Cierre fin de semana', 'slot' => $this->slotOffset(22, 30), 'color' => '#f2994a'],
        ];

        return view('admin.shifts.index', [
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'roleBlocks' => $roleBlocks,
            'hourMarks' => $hourMarks,
            'closingLabel' => $closingLabel,
            'businessMarks' => $businessMarks,
            'totalSlots' => self::GRID_TOTAL_SLOTS,
            'hasConflicts' => $conflictIds->isNotEmpty(),
            'conflictShifts' => $shifts->whereIn('id', $conflictIds->all())->sortBy('start_time'),
            'hoursSummary' => $this->hoursSummary($shifts),
            'monthlySummary' => $this->monthlySummary($anchor, $staffId),
            'monthLabel' => ucfirst($anchor->locale('es')->isoFormat('MMMM YYYY')),
            'staffList' => Staff::where('is_active', true)->orderBy('role')->orderBy('name')->get(),
            'selectedStaffId' => $staffId,
            'staffByRole' => Staff::where('is_active', true)->orderBy('role')->orderBy('name')->get()->groupBy('role'),
            'prevWeek' => $weekStart->copy()->subWeek()->toDateString(),
            'nextWeek' => $weekStart->copy()->addWeek()->toDateString(),
            'isCurrentWeek' => now('America/Santiago')->between($weekStart, $weekEnd),
        ]);
    }

    /**
     * A qué bloque de 15 min (desde GRID_START_HOUR) corresponde una hora
     * del día. Se usa para ubicar las líneas de apertura/cierre en la
     * grilla -- las mismas cuentas que gridPosition() pero para una hora
     * suelta en vez de un turno completo.
     */
    private function slotOffset(int $hour, int $minute): int
    {
        $gridStartMinutes = self::GRID_START_HOUR * 60;

        return intdiv(($hour * 60 + $minute) - $gridStartMinutes, self::SLOT_MINUTES);
    }

    /**
     * Posición del turno en la grilla horaria: en qué bloque de 15 min
     * arranca y cuántos bloques ocupa. Un turno que cruza medianoche (ej.
     * 22:30 a 03:00) se estira más allá de la hora 24 en la MISMA columna
     * del día en que empezó -- así se ve como un solo bloque continuo, no
     * cortado en dos días.
     *
     * @return array{0: int, 1: int}
     */
    private function gridPosition(Shift $shift): array
    {
        $toMinutes = fn (string $time) => ((int) substr($time, 0, 2)) * 60 + ((int) substr($time, 3, 2));

        $startMinutes = $toMinutes($shift->start_time);
        $endMinutes = $toMinutes($shift->end_time);
        if ($shift->crossesMidnight()) {
            $endMinutes += 24 * 60;
        }

        $gridStartMinutes = self::GRID_START_HOUR * 60;
        $startSlot = intdiv($startMinutes - $gridStartMinutes, self::SLOT_MINUTES);
        $span = max(1, intdiv($endMinutes - $startMinutes, self::SLOT_MINUTES));

        // Si por algún motivo cae fuera del eje visible, se recorta en vez
        // de romper la grilla.
        $startSlot = max(0, min($startSlot, self::GRID_TOTAL_SLOTS - 1));

        return [$startSlot, $span];
    }

    /**
     * Mismo personal con dos turnos cuyo horario real se superpone (usa
     * startsAt/endsAt, que ya resuelven turnos que cruzan medianoche).
     *
     * @return Collection<int, int> IDs de los turnos en conflicto
     */
    private function detectConflicts(Collection $shifts): Collection
    {
        $conflicts = collect();

        foreach ($shifts->groupBy('staff_id') as $personShifts) {
            $list = $personShifts->values();
            for ($i = 0; $i < $list->count(); $i++) {
                for ($j = $i + 1; $j < $list->count(); $j++) {
                    $a = $list[$i];
                    $b = $list[$j];
                    if ($a->startsAt()->lt($b->endsAt()) && $b->startsAt()->lt($a->endsAt())) {
                        $conflicts->push($a->id);
                        $conflicts->push($b->id);
                    }
                }
            }
        }

        return $conflicts->unique();
    }

    /**
     * @return Collection<int, array{staff: Staff, assigned: float, legal: ?int, extra: ?float}>
     */
    private function hoursSummary(Collection $shifts): Collection
    {
        return $shifts->groupBy('staff_id')
            ->map(function (Collection $personShifts) {
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
    }

    /**
     * Resumen del mes calendario que contiene $anchor -- las horas legales
     * se prorratean por día (horas semanales / 7 x días del mes) ya que un
     * mes no tiene un número parejo de semanas.
     *
     * @return Collection<int, array{staff: Staff, assigned: float, legal: ?float, extra: ?float}>
     */
    private function monthlySummary(Carbon $anchor, ?int $staffId): Collection
    {
        $monthStart = $anchor->copy()->startOfMonth();
        $monthEnd = $anchor->copy()->endOfMonth();
        $daysInMonth = $monthStart->daysInMonth;

        $query = Shift::with('staff')->whereBetween('date', [$monthStart->toDateString(), $monthEnd->toDateString()]);
        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        return $query->get()->groupBy('staff_id')
            ->map(function (Collection $personShifts) use ($daysInMonth) {
                $staff = $personShifts->first()->staff;
                $assigned = round($personShifts->sum(fn (Shift $s) => $s->durationHours()), 1);
                $legal = $staff->legal_hours_per_week !== null
                    ? round($staff->legal_hours_per_week / 7 * $daysInMonth, 1)
                    : null;

                return [
                    'staff' => $staff,
                    'assigned' => $assigned,
                    'legal' => $legal,
                    'extra' => $legal !== null ? round(max(0, $assigned - $legal), 1) : null,
                ];
            })
            ->sortBy(fn (array $row) => $row['staff']->role.$row['staff']->name)
            ->values();
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
