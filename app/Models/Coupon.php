<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code', 'auto_apply', 'internal_name', 'discount_type', 'discount_value', 'min_amount', 'max_discount_amount',
    'starts_at', 'ends_at', 'allowed_weekdays', 'allowed_time_start', 'allowed_time_end', 'allowed_durations',
    'max_uses_total', 'max_uses_per_customer', 'is_stackable', 'requires_verification', 'verification_note',
    'min_age', 'is_active',
])]
class Coupon extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'starts_at' => 'date',
            'ends_at' => 'date',
            'allowed_weekdays' => 'array',
            'allowed_durations' => 'array',
            'auto_apply' => 'boolean',
            'is_stackable' => 'boolean',
            'requires_verification' => 'boolean',
            'min_age' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** Nombre que ve recepción/cliente para esta oferta o cupón. */
    public function label(): string
    {
        return $this->auto_apply ? $this->internal_name : ($this->code ?? $this->internal_name);
    }

    public function scopeOffers($query)
    {
        return $query->where('auto_apply', true);
    }

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class, 'coupon_rooms');
    }

    public function roomCategories(): BelongsToMany
    {
        return $this->belongsToMany(RoomCategory::class, 'coupon_room_categories');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(CouponRedemption::class);
    }

    /**
     * Alcance explícito de habitaciones/categorías vacío = aplica a todas.
     */
    public function appliesToRoom(Room $room): bool
    {
        $roomScope = $this->rooms()->pluck('rooms.id');
        $categoryScope = $this->roomCategories()->pluck('room_categories.id');

        if ($roomScope->isEmpty() && $categoryScope->isEmpty()) {
            return true;
        }

        return $roomScope->contains($room->id) || $categoryScope->contains($room->room_category_id);
    }

    /**
     * ¿Esta oferta está vigente para esta habitación en este momento? — para
     * el badge del tablero. No mira hora ni duración (eso es de cada reserva),
     * solo fecha, día de la semana y alcance. Requiere rooms/roomCategories
     * precargados para no pegarle a la BD por habitación.
     */
    public function offerLiveFor(Room $room, \Carbon\Carbon $when): bool
    {
        if (! $this->auto_apply || ! $this->is_active) {
            return false;
        }
        if ($this->starts_at && $when->toDateString() < $this->starts_at->toDateString()) {
            return false;
        }
        if ($this->ends_at && $when->toDateString() > $this->ends_at->toDateString()) {
            return false;
        }
        if ($this->allowed_weekdays && ! in_array($when->dayOfWeek, $this->allowed_weekdays, true)) {
            return false;
        }

        $roomIds = $this->relationLoaded('rooms') ? $this->rooms->pluck('id') : $this->rooms()->pluck('rooms.id');
        $catIds = $this->relationLoaded('roomCategories') ? $this->roomCategories->pluck('id') : $this->roomCategories()->pluck('room_categories.id');

        if ($roomIds->isEmpty() && $catIds->isEmpty()) {
            return true;
        }

        return $roomIds->contains($room->id) || $catIds->contains($room->room_category_id);
    }
}
