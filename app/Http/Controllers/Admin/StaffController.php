<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        return view('admin.staff.index', [
            'staff' => Staff::orderBy('role')->orderBy('name')->get(),
            'accounts' => User::orderBy('role')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:50'],
            'legal_hours_per_week' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $person = Staff::create($validated);
        AuditLog::record(auth()->id(), 'personal.crear', 'Staff', $person->id, null, $person->toArray());

        return redirect()->route('admin.staff.index')->with('status', 'Persona agregada.');
    }

    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['required', 'string', 'max:50'],
            'legal_hours_per_week' => ['nullable', 'integer', 'min:0', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $old = $staff->toArray();
        $staff->update($validated);
        AuditLog::record(auth()->id(), 'personal.editar', 'Staff', $staff->id, $old, $staff->toArray());

        return redirect()->route('admin.staff.index')->with('status', 'Datos actualizados.');
    }
}
