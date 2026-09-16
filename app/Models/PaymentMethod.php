<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'name', 'requires_external_id', 'is_active'])]
class PaymentMethod extends Model
{
    protected function casts(): array
    {
        return [
            'requires_external_id' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
