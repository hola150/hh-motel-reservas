<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'role', 'pin', 'last_qr_room_id', 'last_qr_seen_at', 'legal_hours_per_week', 'is_active'])]
class Staff extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'pin' => 'hashed',
            'last_qr_seen_at' => 'datetime',
        ];
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function lastQrRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'last_qr_room_id');
    }
}
