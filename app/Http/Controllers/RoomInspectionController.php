<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Room;
use App\Services\Booking\RoomInspectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomInspectionController extends Controller
{
    public function create(Room $room): View
    {
        $room->load(['category', 'furniture.category']);
        $lastInspection = $room->inspections()->latest()->first();

        return view('rooms.inspection', ['room' => $room, 'lastInspection' => $lastInspection]);
    }

    public function store(Request $request, Room $room, RoomInspectionService $service): RedirectResponse
    {
        $validated = $request->validate([
            'inspected_by' => ['required', 'string', 'max:100'],
            'shift' => ['required', 'in:Mañana,Tarde,Noche,Madrugada'],
            ...$service->validationRules(),
            'from_ronda' => ['nullable', 'boolean'],
            'from_qr' => ['nullable', 'boolean'],
        ]);

        $inspection = $service->submit($request, $room, $validated, $validated['inspected_by']);

        AuditLog::record(auth()->id(), 'habitacion.inspeccionar', 'Room', $room->id, null, $inspection->toArray());

        $statusMessage = $inspection->needs_maintenance
            ? 'Inspección guardada — '.$room->name.' necesita mantención.'
            : 'Inspección guardada — '.$room->name.' está en buen estado.';

        if ($request->boolean('from_ronda')) {
            return redirect()->route('shift_round.index')->with('status', $statusMessage);
        }

        // Si vino del QR de la puerta (anfitrión escaneando esa habitación en
        // particular, no navegando desde el tablero), vuelve ahí mismo en vez
        // de mandarlo al tablero -- sigue en el mismo lugar físico.
        if ($request->boolean('from_qr')) {
            return redirect()->route('rooms.qr.show', $room)->with('status', $statusMessage);
        }

        return redirect()->route('rooms.board')->with('status', $statusMessage);
    }

    /**
     * Panel de mantención: última inspección de cada habitación, para ver de
     * un vistazo cuáles necesitan revisión y cuáles nunca se inspeccionaron.
     */
    public function panel(): View
    {
        $rooms = Room::with(['category', 'latestInspection'])
            ->get()
            ->sortBy(fn (Room $room) => sprintf('%03d-%s', $room->category->display_order, $room->name))
            ->values();

        $entries = $rooms->map(fn (Room $room) => [
            'room' => $room,
            'inspection' => $room->latestInspection,
        ]);

        return view('rooms.inspection-panel', [
            'needsMaintenance' => $entries->filter(fn (array $e) => $e['inspection']?->needs_maintenance)->values(),
            'neverInspected' => $entries->filter(fn (array $e) => ! $e['inspection'])->values(),
            'ok' => $entries->filter(fn (array $e) => $e['inspection'] && ! $e['inspection']->needs_maintenance)->values(),
        ]);
    }
}
