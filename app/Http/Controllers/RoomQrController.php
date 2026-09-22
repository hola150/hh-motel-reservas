<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Room;
use App\Models\Staff;
use App\Services\Booking\RoomBoardService;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Páginas públicas (sin login) detrás del QR pegado en la puerta de cada
 * habitación y del QR general de mucamas -- las mucamas son solo un roster
 * en Staff, sin cuenta de acceso, así que esto no puede depender de auth().
 * El único guardrail real es el propio operational_status de la habitación:
 * el formulario de "aseo listo" solo existe cuando la pieza YA está en
 * aseo, no hay nada que reportar (ni nada que romper) fuera de eso.
 */
class RoomQrController extends Controller
{
    private function cleaningStaffNames(): Collection
    {
        return Staff::where('role', 'Mucama')->where('is_active', true)->orderBy('name')->pluck('name');
    }

    public function show(Room $room): View
    {
        return view('rooms.qr-show', [
            'room' => $room,
            'cleaningStaff' => $this->cleaningStaffNames(),
        ]);
    }

    /**
     * PNG del QR para imprimir y pegar en la puerta -- codifica la misma
     * URL pública de show(), nada sensible, así que no hace falta login
     * para verla (igual que las fotos del catálogo).
     */
    public function image(Room $room): Response
    {
        return $this->qrPng(route('rooms.qr.show', $room), $room->name);
    }

    public function mucamaPanelImage(): Response
    {
        return $this->qrPng(route('rooms.qr.mucama_panel'), 'Panel de mucamas');
    }

    private function qrPng(string $url, string $label): Response
    {
        $result = (new Builder(
            writer: new PngWriter(),
            data: $url,
            size: 400,
            margin: 12,
            labelText: $label,
        ))->build();

        return response($result->getString(), 200, ['Content-Type' => $result->getMimeType()]);
    }

    public function reportAseo(Request $request, Room $room): RedirectResponse
    {
        if ($room->operational_status !== 'aseo') {
            return back()->withErrors(['cleaned_by' => 'Esta habitación ya no está esperando aseo -- puede que alguien ya la haya confirmado.']);
        }
        if ($room->aseo_reported_at) {
            return back()->with('status', 'Ya estaba reportada -- recepción todavía tiene que confirmarla.');
        }

        $names = $this->cleaningStaffNames();
        $validated = $request->validate([
            'cleaned_by' => ['required', 'string', Rule::in($names)],
        ], [
            'cleaned_by.required' => 'Falta indicar quién hizo el aseo.',
            'cleaned_by.in' => 'Elegí una persona de aseo válida.',
        ]);

        $old = $room->only(['aseo_reported_by', 'aseo_reported_at']);
        $room->update(['aseo_reported_by' => $validated['cleaned_by'], 'aseo_reported_at' => now()]);
        AuditLog::record(null, 'habitacion.aseo_reportado', 'Room', $room->id, $old, $room->only(['aseo_reported_by', 'aseo_reported_at']));

        return redirect()->route('rooms.qr.show', $room)->with('status', '¡Gracias, '.$validated['cleaned_by'].'! Recepción ya puede confirmarlo.');
    }

    /**
     * Panel general de mucamas -- QR único, no por habitación. Solo lo
     * operativo del día: qué falta hacer y qué viene, nada de cliente ni
     * de dinero.
     */
    public function mucamaPanel(RoomBoardService $board): View
    {
        $entries = collect($board->board());

        $pendingAseo = $entries->filter(fn (array $e) => $e['room']->operational_status === 'aseo' && ! $e['room']->aseo_reported_at)->values();
        $reportedAseo = $entries->filter(fn (array $e) => $e['room']->operational_status === 'aseo' && $e['room']->aseo_reported_at)->values();
        $occupied = $entries->filter(fn (array $e) => $e['room']->operational_status === 'activa' && $e['status']['occupancy'] === 'ocupada')->values();
        $upcoming = $entries->filter(fn (array $e) => $e['room']->operational_status === 'activa' && $e['status']['occupancy'] === 'libre' && $e['status']['next_booking'])
            ->sortBy(fn (array $e) => $e['status']['next_booking']->starts_at)
            ->values();

        return view('rooms.qr-mucama-panel', compact('pendingAseo', 'reportedAseo', 'occupied', 'upcoming'));
    }
}
