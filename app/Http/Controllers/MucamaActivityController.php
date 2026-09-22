<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Room;
use App\Models\Staff;
use App\Models\StaffShiftLog;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

/**
 * "Dónde anda cada mucama ahora" para recepción -- distinto del panel de
 * mucamas (rooms.qr.mucama_panel), que es la vista operativa DE ELLAS. Esto
 * es la vista de seguimiento PARA recepción: a qué habitación fue cada una
 * la última vez y cuándo, con el estado actual de esa pieza como contexto,
 * la ruta de habitaciones que recorrió hoy, y el registro real de entrada/
 * salida de turno (StaffShiftLog).
 */
class MucamaActivityController extends Controller
{
    public function index(): View
    {
        $mucamas = Staff::where('role', 'Mucama')->where('is_active', true)
            ->with(['lastQrRoom.category', 'shiftLogs' => fn ($q) => $q->whereNull('ended_at')])
            ->orderByDesc('last_qr_seen_at')
            ->get();

        $today = now('America/Santiago')->startOfDay();
        $todayLogs = StaffShiftLog::with('staff')
            ->where('started_at', '>=', $today)
            ->orderByDesc('started_at')
            ->get();

        $routesByStaff = $this->todaysRoutes($today);

        return view('mucamas.activity', ['mucamas' => $mucamas, 'todayLogs' => $todayLogs, 'routesByStaff' => $routesByStaff]);
    }

    /**
     * Resumen liviano para el polling del navegador (ver el <script> de la
     * vista) -- así recepción se entera de un cambio sin esperar el
     * refresco completo de la página cada 30s.
     */
    public function status(): JsonResponse
    {
        $mucamas = Staff::where('role', 'Mucama')->where('is_active', true)->get(['id', 'name', 'last_qr_room_id', 'last_qr_seen_at']);
        $roomNames = Room::whereIn('id', $mucamas->pluck('last_qr_room_id')->filter())->pluck('name', 'id');

        return response()->json($mucamas->map(fn (Staff $m) => [
            'id' => $m->id,
            'name' => $m->name,
            'room' => $m->last_qr_room_id ? ($roomNames[$m->last_qr_room_id] ?? null) : null,
            'seen_at' => $m->last_qr_seen_at?->toIso8601String(),
        ])->values());
    }

    /**
     * Ruta de habitaciones que cada mucama recorrió hoy (AuditLog
     * habitacion.qr_visita, escrito por RoomQrController::show() solo
     * cuando cambia de habitación) -- de más antigua a más nueva.
     *
     * @return \Illuminate\Support\Collection<int, \Illuminate\Support\Collection>
     */
    private function todaysRoutes(\Carbon\Carbon $today): \Illuminate\Support\Collection
    {
        $roomIds = [];
        $logs = AuditLog::where('action', 'habitacion.qr_visita')
            ->where('created_at', '>=', $today)
            ->orderBy('created_at')
            ->get()
            ->each(function (AuditLog $log) use (&$roomIds) { $roomIds[] = (int) $log->entity_id; });

        $roomNames = Room::whereIn('id', array_unique($roomIds))->pluck('name', 'id');

        return $logs->groupBy(fn (AuditLog $log) => $log->new_value['staff_id'] ?? 0)
            ->map(fn ($group) => $group->map(fn (AuditLog $log) => [
                'room' => $roomNames[(int) $log->entity_id] ?? '?',
                'at' => $log->created_at->timezone('America/Santiago')->format('H:i'),
            ]));
    }
}
