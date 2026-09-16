<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['rate_rule_id', 'room_category_id', 'duration_minutes', 'price'])]
class RateRulePrice extends Model
{
    public function rateRule(): BelongsTo
    {
        return $this->belongsTo(RateRule::class);
    }

    public function roomCategory(): BelongsTo
    {
        return $this->belongsTo(RoomCategory::class);
    }
}
