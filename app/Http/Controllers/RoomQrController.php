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
use Illuminate\View\View;

/**
 * Páginas detrás del QR pegado en la puerta de cada habitación y del QR
 * general de mucamas. show()/image() son públicas (solo muestran estado, no
 * hay nada sensible ni que romper); reportAseo() y mucamaPanel() exigen
 * sesión de mucama (middleware 'mucama', ver EnsureMucamaSession) -- antes
 * cualquiera con el link podía reportar "a nombre de" cualquier mucama
 * eligiéndola de una lista, ahora reporta la que efectivamente inició sesión.
 */
class RoomQrController extends Controller
{
    public function show(Request $request, Room $room): View
    {
        $mucamaId = $request->session()->get('mucama_staff_id');
        $mucama = $mucamaId ? Staff::where('role', 'Mucama')->where('is_active', true)->find($mucamaId) : null;

        return view('rooms.qr-show', ['room' => $room, 'mucama' => $mucama]);
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
            return back()->withErrors(['pin' => 'Esta habitación ya no está esperando aseo -- puede que alguien ya la haya confirmado.']);
        }
        if ($room->aseo_reported_at) {
            return back()->with('status', 'Ya estaba reportada -- recepción todavía tiene que confirmarla.');
        }

        /** @var Staff $mucama */
        $mucama = $request->attributes->get('mucama');

        $old = $room->only(['aseo_reported_by', 'aseo_reported_at']);
        $room->update(['aseo_reported_by' => $mucama->name, 'aseo_reported_at' => now()]);
        AuditLog::record(null, 'habitacion.aseo_reportado', 'Room', $room->id, $old, $room->only(['aseo_reported_by', 'aseo_reported_at']));

        return redirect()->route('rooms.qr.show', $room)->with('status', '¡Gracias, '.$mucama->name.'! Recepción ya puede confirmarlo.');
    }

    /**
     * Panel general de mucamas -- QR único, no por habitación. Solo lo
     * operativo del día: qué falta hacer y qué viene, nada de cliente ni
     * de dinero.
     */
    public function mucamaPanel(Request $request, RoomBoardService $board): View
    {
        $entries = collect($board->board());

        $pendingAseo = $entries->filter(fn (array $e) => $e['room']->operational_status === 'aseo' && ! $e['room']->aseo_reported_at)->values();
        $reportedAseo = $entries->filter(fn (array $e) => $e['room']->operational_status === 'aseo' && $e['room']->aseo_reported_at)->values();
        $occupied = $entries->filter(fn (array $e) => $e['room']->operational_status === 'activa' && $e['status']['occupancy'] === 'ocupada')->values();
        $upcoming = $entries->filter(fn (array $e) => $e['room']->operational_status === 'activa' && $e['status']['occupancy'] === 'libre' && $e['status']['next_booking'])
            ->sortBy(fn (array $e) => $e['status']['next_booking']->starts_at)
            ->values();

        return view('rooms.qr-mucama-panel', [
            'pendingAseo' => $pendingAseo,
            'reportedAseo' => $reportedAseo,
            'occupied' => $occupied,
            'upcoming' => $upcoming,
            'mucama' => $request->attributes->get('mucama'),
        ]);
    }
}
