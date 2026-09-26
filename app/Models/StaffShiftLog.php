<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['staff_id', 'user_id', 'started_at', 'ended_at'])]
class StaffShiftLog extends Model
{
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mucama (staff_id) o cuenta de acceso (user_id) -- una fila siempre
     * usa una sola de las dos, nunca ambas.
     */
    public function who(): Staff|User|null
    {
        return $this->staff ?? $this->user;
    }

    public function durationMinutes(): int
    {
        return (int) $this->started_at->diffInMinutes($this->ended_at ?? now());
    }
}
