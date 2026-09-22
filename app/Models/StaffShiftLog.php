<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['staff_id', 'started_at', 'ended_at'])]
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

    public function durationMinutes(): int
    {
        return (int) $this->started_at->diffInMinutes($this->ended_at ?? now());
    }
}
