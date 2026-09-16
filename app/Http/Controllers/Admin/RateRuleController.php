<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\RateRule;
use App\Models\RateRulePrice;
use App\Models\RoomCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RateRuleController extends Controller
{
    public function index(): View
    {
        $categories = RoomCategory::orderBy('display_order')->get();
        $rateRules = RateRule::with('prices.roomCategory')->orderByDesc('priority')->get();

        // Grilla categoría x duración por tarifa, para ver los precios de un
        // vistazo sin entrar a "Editar precios" de cada una.
        $priceGrids = $rateRules->mapWithKeys(function (RateRule $rule) {
            $durations = $rule->prices->pluck('duration_minutes')->unique()->sort()->values();
            $grid = $rule->prices->groupBy('room_category_id')
                ->map(fn ($prices) => $prices->keyBy('duration_minutes'));

            return [$rule->id => ['durations' => $durations, 'grid' => $grid]];
        });

        return view('admin.rates.index', [
            'rateRules' => $rateRules,
            'categories' => $categories,
            'priceGrids' => $priceGrids,
        ]);
    }

    public function edit(RateRule $rateRule): View
    {
        return view('admin.rates.edit', [
            'rateRule' => $rateRule,
            'prices' => $rateRule->prices()->with('roomCategory')->get()->sortBy(fn ($p) => [$p->roomCategory->display_order, $p->duration_minutes]),
            'categories' => RoomCategory::orderBy('display_order')->get(),
        ]);
    }

    public function update(Request $request, RateRule $rateRule): RedirectResponse
    {
        $validated = $request->validate([
            'extra_person_price' => ['required', 'integer', 'min:0'],
            'extra_hour_price' => ['required', 'integer', 'min:0'],
            'priority' => ['required', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $old = $rateRule->toArray();
        $rateRule->update($validated);
        AuditLog::record(auth()->id(), 'tarifa.editar', 'RateRule', $rateRule->id, $old, $rateRule->toArray());

        return redirect()->route('admin.rates.edit', $rateRule)->with('status', 'Tarifa actualizada.');
    }

    public function updatePrice(Request $request, RateRulePrice $price): RedirectResponse
    {
        $validated = $request->validate(['price' => ['required', 'integer', 'min:0']]);

        $old = $price->toArray();
        $price->update($validated);
        AuditLog::record(auth()->id(), 'tarifa.precio_editar', 'RateRulePrice', $price->id, $old, $price->toArray());

        return redirect()->route('admin.rates.edit', $price->rate_rule_id)->with('status', 'Precio actualizado.');
    }

    public function storePrice(Request $request, RateRule $rateRule): RedirectResponse
    {
        $validated = $request->validate([
            'room_category_id' => ['required', 'exists:room_categories,id'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
            'price' => ['required', 'integer', 'min:0'],
        ]);
        $validated['rate_rule_id'] = $rateRule->id;

        $price = RateRulePrice::updateOrCreate(
            ['rate_rule_id' => $rateRule->id, 'room_category_id' => $validated['room_category_id'], 'duration_minutes' => $validated['duration_minutes']],
            ['price' => $validated['price']]
        );
        AuditLog::record(auth()->id(), 'tarifa.precio_crear', 'RateRulePrice', $price->id, null, $price->toArray());

        return redirect()->route('admin.rates.edit', $rateRule)->with('status', 'Precio agregado.');
    }

    public function destroyPrice(RateRulePrice $price): RedirectResponse
    {
        $rateRuleId = $price->rate_rule_id;
        AuditLog::record(auth()->id(), 'tarifa.precio_eliminar', 'RateRulePrice', $price->id, $price->toArray(), null);
        $price->delete();

        return redirect()->route('admin.rates.edit', $rateRuleId)->with('status', 'Precio eliminado.');
    }
}
