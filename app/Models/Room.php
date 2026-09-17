<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['room_category_id', 'name', 'wing', 'photos', 'videos', 'buffer_minutes', 'operational_status', 'operational_note', 'aseo_override_at', 'aseo_started_at'])]
class Room extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'videos' => 'array',
            'aseo_override_at' => 'datetime',
            'aseo_started_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class, 'room_category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function furniture(): BelongsToMany
    {
        return $this->belongsToMany(FurnitureItem::class, 'room_furniture')
            ->withPivot(['quantity', 'condition', 'notes'])->withTimestamps();
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(RoomInspection::class);
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(RoomInspection::class)->latestOfMany();
    }

    public function isOperational(): bool
    {
        return $this->operational_status === 'activa';
    }

    /**
     * Cuando el Ala Sur está apagada (ver OperationalSetting), sus
     * habitaciones dejan de ofrecerse para reservas nuevas. Las de "siempre
     * activas" (wing null) y Ala Norte nunca se filtran acá.
     */
    public function scopeWingEnabled($query, bool $alaSurEnabled)
    {
        return $alaSurEnabled ? $query : $query->where(fn ($q) => $q->whereNull('wing')->orWhere('wing', '!=', 'sur'));
    }

    /**
     * Espejo de scopeWingEnabled pero por categoría: cuando se apaga una
     * categoría entera desde el tablero (RoomCategory::is_active = false),
     * sus habitaciones dejan de ofrecerse para reservas nuevas.
     */
    public function scopeCategoryEnabled($query)
    {
        return $query->whereHas('category', fn ($q) => $q->where('is_active', true));
    }

    /**
     * Reserva vigente en este instante (para calcular ocupación en el tablero).
     */
    public function currentBooking(): ?Booking
    {
        return $this->bookings()
            ->whereNotIn('booking_status', ['CANCELADA', 'EXPIRADA', 'NO_SHOW'])
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>', now())
            ->first();
    }
}
