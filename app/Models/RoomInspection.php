<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Checklist de inspección de una habitación en un momento dado -- lo llena
 * recepción para saber si una pieza necesita mantención antes de volver a
 * ofrecerla. Cada fila es una inspección puntual, no un estado editable: el
 * historial completo queda a la vista por habitación.
 */
#[Fillable(['room_id', 'inspected_by', 'checklist', 'needs_maintenance', 'notes'])]
class RoomInspection extends Model
{
    /**
     * Ítems fijos del checklist -- clave usada en el JSON guardado y label
     * mostrado en el formulario. Si un ítem queda marcado "falla", la
     * inspección completa se marca needs_maintenance.
     *
     * @var array<string, string>
     */
    public const ITEMS = [
        'sabanas_toallas' => 'Sábanas y toallas limpias',
        'bano' => 'Baño limpio y sin fugas',
        'iluminacion' => 'Iluminación funcionando',
        'clima' => 'Aire acondicionado / calefacción OK',
        'tv_control' => 'TV y control remoto OK',
        'cerradura_llave' => 'Cerradura y llave OK',
        'olor_danos' => 'Sin olores raros ni daños visibles',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'needs_maintenance' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
