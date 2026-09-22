<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Room;
use App\Models\RoomInspection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class RoomInspectionController extends Controller
{
    public function create(Room $room): View
    {
        $room->load(['category', 'furniture.category']);
        $lastInspection = $room->inspections()->latest()->first();

        return view('rooms.inspection', ['room' => $room, 'lastInspection' => $lastInspection]);
    }

    public function store(Request $request, Room $room): RedirectResponse
    {
        $itemKeys = array_keys(RoomInspection::ITEMS);

        // "checklist.*" no alcanza -- también matchea "checklist.furniture"
        // (que es un array de ítems, no "ok"/"falla") y esa fila siempre
        // fallaba la regla in:ok,falla. Los ítems fijos y el mobiliario se
        // validan por separado.
        $validated = $request->validate([
            'inspected_by' => ['required', 'string', 'max:100'],
            'shift' => ['required', 'in:Mañana,Tarde,Noche,Madrugada'],
            'checklist' => ['required', 'array'],
            ...collect($itemKeys)->mapWithKeys(fn (string $key) => ["checklist.{$key}" => ['required', 'in:ok,falla']])->all(),
            'checklist.furniture' => ['nullable', 'array'],
            'checklist.furniture.*' => ['required', 'in:ok,falla'],
            'notes' => ['nullable', 'string', 'max:500'],
            'defects' => ['nullable', 'string', 'max:1000'],
            'photos' => ['nullable', 'array', 'max:6'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'from_ronda' => ['nullable', 'boolean'],
        ]);

        // Cada ítem del checklist fijo tiene que venir marcado -- si falta
        // alguno (formulario incompleto), se cuenta como "falla" para no
        // dejar pasar una inspección a medias como si estuviera todo bien.
        $checklist = collect($itemKeys)->mapWithKeys(
            fn (string $key) => [$key => $validated['checklist'][$key] ?? 'falla']
        )->all();
        $checklist['furniture'] = collect($room->furniture)->mapWithKeys(fn ($item) => [
            (string) $item->id => $validated['checklist']['furniture'][$item->id] ?? 'falla',
        ])->all();

        $needsMaintenance = in_array('falla', $checklist, true) || in_array('falla', $checklist['furniture'], true);
        $photos = collect($request->file('photos', []))->map(fn ($photo) => $photo->store('inspections', 'public'))->values()->all();

        $data = [
            'room_id' => $room->id,
            'inspected_by' => $validated['inspected_by'],
            'checklist' => $checklist,
            'needs_maintenance' => $needsMaintenance,
            'notes' => $validated['notes'] ?? null,
        ];
        if (Schema::hasColumn('room_inspections', 'shift')) $data['shift'] = $validated['shift'];
        if (Schema::hasColumn('room_inspections', 'defects')) $data['defects'] = $validated['defects'] ?? null;
        if (Schema::hasColumn('room_inspections', 'photos')) $data['photos'] = $photos;
        $inspection = RoomInspection::create($data);

        AuditLog::record(auth()->id(), 'habitacion.inspeccionar', 'Room', $room->id, null, $inspection->toArray());

        $statusMessage = $needsMaintenance
            ? 'Inspección guardada — '.$room->name.' necesita mantención.'
            : 'Inspección guardada — '.$room->name.' está en buen estado.';

        if ($request->boolean('from_ronda')) {
            return redirect()->route('shift_round.index')->with('status', $statusMessage);
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
