<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['date', 'forced_rate_rule_id', 'reason'])]
class CalendarOverride extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function forcedRateRule(): BelongsTo
    {
        return $this->belongsTo(RateRule::class, 'forced_rate_rule_id');
    }
}
