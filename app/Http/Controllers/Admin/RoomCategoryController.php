<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\OperationalSetting;
use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'categories' => RoomCategory::orderBy('display_order')->get(),
            'settings' => OperationalSetting::current(),
        ]);
    }

    /**
     * Prende/apaga la oferta del Ala Sur -- deja de ofrecerse para reservas
     * nuevas sin tocar las habitaciones que ya estan ocupadas, por llegar,
     * en aseo o fuera de servicio ahi. Antes vivia en el tablero; se mueve
     * acá para que no sea responsabilidad de quien está en recepción.
     */
    public function toggleAlaSur(): RedirectResponse
    {
        $setting = OperationalSetting::current();
        $setting->update(['ala_sur_enabled' => ! $setting->ala_sur_enabled]);

        return redirect()->route('admin.categories.index')->with('status', $setting->ala_sur_enabled
            ? 'Ala Sur habilitada — vuelve a ofrecerse en el tablero.'
            : 'Ala Sur deshabilitada — sus habitaciones libres ya no se ofrecen.');
    }

    /**
     * Espejo de toggleAlaSur pero por categoría (mismo campo que ya
     * controla si la categoría aparece en el catálogo público -- una
     * categoría "inactiva" lo es en todos lados, no solo en el tablero).
     */
    public function toggleCategory(RoomCategory $category): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);

        return redirect()->route('admin.categories.index')->with('status', $category->is_active
            ? "{$category->name} habilitada — vuelve a ofrecerse en el tablero y el catálogo."
            : "{$category->name} deshabilitada — sus habitaciones libres ya no se ofrecen.");
    }

    /**
     * Espejo de toggleAlaSur pero por piso (1/2/3, según los últimos 3
     * dígitos del nombre de la habitación).
     */
    public function toggleFloor(int $floor): RedirectResponse
    {
        abort_unless(in_array($floor, [1, 2, 3], true), 404);

        $setting = OperationalSetting::current();
        $field = "piso_{$floor}_enabled";
        $setting->update([$field => ! $setting->$field]);

        return redirect()->route('admin.categories.index')->with('status', $setting->$field
            ? "Piso {$floor} habilitado — vuelve a ofrecerse en el tablero."
            : "Piso {$floor} deshabilitado — sus habitaciones libres ya no se ofrecen.");
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
