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
#[Fillable(['room_id', 'inspected_by', 'shift', 'checklist', 'needs_maintenance', 'notes', 'defects', 'photos'])]
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
        'toallas' => 'Toallas',
        'estufa' => 'Estufa',
        'espejos' => 'Espejos',
        'flogger' => 'Flogger',
        'confort_ropa_cama' => 'Confort / ropa de cama',
        'jabon_shampoo' => 'Jabón / shampoo',
        'luces' => 'Luces',
        'olor' => 'Olor',
        'ceniceros' => 'Ceniceros',
        'pilas_chapa' => 'Pilas de chapa',
        'reponer_aseo' => 'Reponer artículos de aseo',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'photos' => 'array',
            'needs_maintenance' => 'boolean',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
