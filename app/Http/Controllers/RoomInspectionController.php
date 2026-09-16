<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Room;
use App\Models\RoomInspection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomInspectionController extends Controller
{
    public function create(Room $room): View
    {
        $room->load('category');
        $lastInspection = $room->inspections()->latest()->first();

        return view('rooms.inspection', ['room' => $room, 'lastInspection' => $lastInspection]);
    }

    public function store(Request $request, Room $room): RedirectResponse
    {
        $itemKeys = array_keys(RoomInspection::ITEMS);

        $validated = $request->validate([
            'inspected_by' => ['required', 'string', 'max:100'],
            'checklist' => ['required', 'array'],
            'checklist.*' => ['required', 'in:ok,falla'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Cada ítem del checklist fijo tiene que venir marcado -- si falta
        // alguno (formulario incompleto), se cuenta como "falla" para no
        // dejar pasar una inspección a medias como si estuviera todo bien.
        $checklist = collect($itemKeys)->mapWithKeys(
            fn (string $key) => [$key => $validated['checklist'][$key] ?? 'falla']
        )->all();

        $needsMaintenance = in_array('falla', $checklist, true);

        $inspection = RoomInspection::create([
            'room_id' => $room->id,
            'inspected_by' => $validated['inspected_by'],
            'checklist' => $checklist,
            'needs_maintenance' => $needsMaintenance,
            'notes' => $validated['notes'] ?? null,
        ]);

        AuditLog::record(auth()->id(), 'habitacion.inspeccionar', 'Room', $room->id, null, $inspection->toArray());

        return redirect()->route('rooms.board')
            ->with('status', $needsMaintenance
                ? 'Inspección guardada — '.$room->name.' necesita mantención.'
                : 'Inspección guardada — '.$room->name.' está en buen estado.');
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
