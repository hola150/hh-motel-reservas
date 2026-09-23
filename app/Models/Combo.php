<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'price', 'display_order', 'is_active'])]
class Combo extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(ComboItem::class);
    }

    public function bookingAddons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }

    /**
     * Cuántas unidades del combo se pueden armar hoy con el stock actual —
     * el mínimo entre los componentes que sí controlan inventario. Null si
     * ningún componente controla stock (combo "ilimitado").
     */
    public function availableCount(): ?int
    {
        $limits = $this->items->map(function (ComboItem $item) {
            if (! $item->product->track_inventory) {
                return null;
            }

            return intdiv($item->product->stock, max(1, $item->quantity));
        })->filter(fn ($v) => $v !== null);

        return $limits->isEmpty() ? null : $limits->min();
    }

    public function isOutOfStock(): bool
    {
        $available = $this->availableCount();

        return $available !== null && $available <= 0;
    }
}
