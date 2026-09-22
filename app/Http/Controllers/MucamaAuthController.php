<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Login liviano por PIN para el panel/QR de mucamas -- no es el guard de
 * auth() de Laravel (Staff no es un usuario del sistema), solo una sesión
 * propia (mucama_staff_id) para que cada una entre como ella misma en vez
 * de elegir cualquier nombre de una lista.
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
            'staff_id.required' => 'Elegí tu nombre.',
            'pin.digits' => 'El PIN tiene 4 dígitos.',
        ]);

        $staff = Staff::where('role', 'Mucama')->where('is_active', true)->find($validated['staff_id']);

        if (! $staff || ! $staff->pin || ! Hash::check($validated['pin'], $staff->pin)) {
            return back()->withErrors(['pin' => 'PIN incorrecto -- pedile a recepción que te lo revise en Personal.'])->withInput();
        }

        $request->session()->put('mucama_staff_id', $staff->id);
        $request->session()->regenerate();

        return redirect()->to($validated['next'] ?? route('rooms.qr.mucama_panel'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('mucama_staff_id');

        return redirect()->route('mucamas.login');
    }
}
