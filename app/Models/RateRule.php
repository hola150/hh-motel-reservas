<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'extra_person_price', 'extra_hour_price', 'priority', 'valid_from', 'valid_until', 'is_active'])]
class RateRule extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_until' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function windows(): HasMany
    {
        return $this->hasMany(RateRuleWindow::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(RateRulePrice::class);
    }
}
