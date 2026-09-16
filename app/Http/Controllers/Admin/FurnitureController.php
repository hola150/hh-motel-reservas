<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\FurnitureCategory;
use App\Models\FurnitureItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FurnitureController extends Controller
{
    public function index(): View
    {
        return view('admin.furniture.index', [
            'categories' => FurnitureCategory::with(['items' => fn ($q) => $q->orderBy('name')])->orderBy('name')->get(),
        ]);
    }

    public function category(Request $request, ?FurnitureCategory $category = null): RedirectResponse
    {
        $category ??= new FurnitureCategory();
        $data = $request->validate(['name' => ['required', 'string', 'max:100', Rule::unique('furniture_categories')->ignore($category->id)]]);
        DB::transaction(function () use ($category, $data) {
            $old = $category->exists ? $category->toArray() : null;
            $category->fill($data)->save();
            AuditLog::record(auth()->id(), 'mobiliario.categoria_guardar', 'FurnitureCategory', $category->id, $old, $category->toArray());
        });
        return redirect()->route('admin.furniture.index')->with('status', 'Categoría de mobiliario guardada.');
    }

    public function item(Request $request, ?FurnitureItem $item = null): RedirectResponse
    {
        $item ??= new FurnitureItem();
        $data = $request->validate([
            'furniture_category_id' => ['required', 'integer', 'exists:furniture_categories,id'],
            'name' => ['required', 'string', 'max:100', Rule::unique('furniture_items')->where('furniture_category_id', $request->input('furniture_category_id'))->ignore($item->id)],
            'icon' => ['nullable', 'string', 'max:12'],
        ]);
        DB::transaction(function () use ($item, $data) {
            $old = $item->exists ? $item->toArray() : null;
            $item->fill($data)->save();
            AuditLog::record(auth()->id(), 'mobiliario.elemento_guardar', 'FurnitureItem', $item->id, $old, $item->toArray());
        });
        return redirect()->route('admin.furniture.index')->with('status', 'Elemento guardado. Ya puedes asignarlo a las habitaciones.');
    }
}
