<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Fila única (id=1) con interruptores operativos globales del motel.
 */
#[Fillable(['ala_sur_enabled', 'piso_1_enabled', 'piso_2_enabled', 'piso_3_enabled'])]
class OperationalSetting extends Model
{
    protected function casts(): array
    {
        return [
            'ala_sur_enabled' => 'boolean',
            'piso_1_enabled' => 'boolean',
            'piso_2_enabled' => 'boolean',
            'piso_3_enabled' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    /**
     * Pisos actualmente deshabilitados (1, 2 y/o 3) según los interruptores.
     *
     * @return array<int, int>
     */
    public function disabledFloors(): array
    {
        return collect([1 => $this->piso_1_enabled, 2 => $this->piso_2_enabled, 3 => $this->piso_3_enabled])
            ->reject(fn (bool $enabled) => $enabled)
            ->keys()
            ->all();
    }
}
