<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fila única (id=1) con interruptores operativos globales del motel.
 */
#[Fillable(['ala_sur_enabled'])]
class OperationalSetting extends Model
{
    protected function casts(): array
    {
        return [
            'ala_sur_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }
}
