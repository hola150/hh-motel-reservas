<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rate_rule_id', 'weekday', 'start_time', 'end_time', 'wraps_midnight'])]
class RateRuleWindow extends Model
{
    protected function casts(): array
    {
        return [
            'wraps_midnight' => 'boolean',
        ];
    }

    public function rateRule(): BelongsTo
    {
        return $this->belongsTo(RateRule::class);
    }
}
