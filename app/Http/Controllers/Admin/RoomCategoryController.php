<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => RoomCategory::orderBy('display_order')->get()]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new RoomCategory()]);
    }

    public function edit(RoomCategory $category): View
    {
        return view('admin.categories.form', ['category' => $category]);
    }

    public function store(Request $request): RedirectResponse
    {
        return $this->save($request, new RoomCategory());
    }

    public function update(Request $request, RoomCategory $category): RedirectResponse
    {
        return $this->save($request, $category);
    }

    private function save(Request $request, RoomCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'former_name' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'string'],
            'base_capacity' => ['required', 'integer', 'min:1'],
            'extra_guest_from' => ['required', 'integer', 'min:1'],
            'display_order' => ['required', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $validated['features'] = $validated['features']
            ? array_values(array_filter(array_map('trim', explode("\n", $validated['features']))))
            : [];
        $validated['is_active'] = $request->boolean('is_active');

        $old = $category->exists ? $category->toArray() : null;
        $category->fill($validated)->save();
        AuditLog::record(auth()->id(), $old ? 'categoria.editar' : 'categoria.crear', 'RoomCategory', $category->id, $old, $category->toArray());

        return redirect()->route('admin.categories.index')->with('status', 'Categoría guardada.');
    }
}
