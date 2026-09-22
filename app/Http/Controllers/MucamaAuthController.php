<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffShiftLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Login liviano por PIN para el panel/QR de mucamas -- no es el guard de
 * auth() de Laravel (Staff no es un usuario del sistema), solo una sesión
 * propia (mucama_staff_id) para que cada una entre como ella misma en vez
 * de elegir cualquier nombre de una lista.
 *
 * De paso, login/logout funcionan como marca de entrada/salida real
 * (StaffShiftLog) -- distinto del horario planificado (Shift): esto es lo
 * que pasó de verdad, no lo agendado.
 */
class MucamaAuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        return view('mucamas.login', [
            'staff' => Staff::where('role', 'Mucama')->where('is_active', true)->orderBy('name')->get(),
            'next' => $request->query('next', route('rooms.qr.mucama_panel')),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'integer'],
            'pin' => ['required', 'digits:4'],
            'next' => ['nullable', 'string'],
        ], [
            'staff_id.required' => 'Elige tu nombre.',
            'pin.digits' => 'El PIN tiene 4 dígitos.',
        ]);

        $staff = Staff::where('role', 'Mucama')->where('is_active', true)->find($validated['staff_id']);

        if (! $staff || ! $staff->pin || ! Hash::check($validated['pin'], $staff->pin)) {
            return back()->withErrors(['pin' => 'PIN incorrecto -- pedile a recepción que te lo revise en Personal.'])->withInput();
        }

        $request->session()->put('mucama_staff_id', $staff->id);
        $request->session()->regenerate();

        // Si ya tenía un turno abierto (por ejemplo, volvió a loguearse sin
        // haber cerrado sesión antes) no se abre uno nuevo -- un login =
        // un turno, no uno por cada vez que entra a mirar algo.
        if (! StaffShiftLog::where('staff_id', $staff->id)->whereNull('ended_at')->exists()) {
            StaffShiftLog::create(['staff_id' => $staff->id, 'started_at' => now()]);
        }

        return redirect()->to($validated['next'] ?? route('rooms.qr.mucama_panel'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $staffId = $request->session()->get('mucama_staff_id');
        if ($staffId) {
            StaffShiftLog::where('staff_id', $staffId)->whereNull('ended_at')
                ->latest('started_at')->first()?->update(['ended_at' => now()]);
        }

        $request->session()->forget('mucama_staff_id');

        return redirect()->route('mucamas.login');
    }
}
