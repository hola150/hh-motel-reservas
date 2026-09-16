<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['room_category_id', 'name', 'photos', 'buffer_minutes', 'operational_status', 'operational_note', 'aseo_override_at', 'aseo_started_at'])]
class Room extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'photos' => 'array',
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
