<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'type', 'price', 'from_room_category_id', 'to_room_category_id',
    'extra_minutes', 'combo_id', 'display_order', 'is_active',
])]
class UpsellOffer extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function fromCategory(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'from_room_category_id');
    }

    public function toCategory(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'to_room_category_id');
    }

    public function combo(): BelongsTo
    {
        return $this->belongsTo(Combo::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Lo que efectivamente se cobra por este upsell. */
    public function chargeAmount(): int
    {
        return $this->type === 'combo' ? (int) ($this->combo?->price ?? 0) : (int) $this->price;
    }
}
