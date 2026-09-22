<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffShiftLog;
use Illuminate\View\View;

/**
 * "Dónde anda cada mucama ahora" para recepción -- distinto del panel de
 * mucamas (rooms.qr.mucama_panel), que es la vista operativa DE ELLAS. Esto
 * es la vista de seguimiento PARA recepción: a qué habitación fue cada una
 * la última vez y cuándo, con el estado actual de esa pieza como contexto,
 * más el registro real de entrada/salida de turno (StaffShiftLog).
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

        return view('mucamas.activity', ['mucamas' => $mucamas, 'todayLogs' => $todayLogs]);
    }
}
