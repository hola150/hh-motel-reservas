<?php

namespace App\Http\Middleware;

use App\Models\Staff;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gatekeeper del panel/QR de mucamas -- re-valida en cada request (no solo
 * al loguearse) que la sesión siga apuntando a una mucama activa, por si la
 * desactivaron en Personal mientras tenía la sesión abierta en el celular.
 */
class EnsureMucamaSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $staffId = $request->session()->get('mucama_staff_id');
        $staff = $staffId ? Staff::activeMucamas()->find($staffId) : null;

        if (! $staff) {
            $request->session()->forget('mucama_staff_id');

            return redirect()->route('mucamas.login', ['next' => $request->fullUrl()]);
        }

        $request->attributes->set('mucama', $staff);

        return $next($request);
    }
}
