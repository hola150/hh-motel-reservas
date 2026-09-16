<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['staff_id', 'date', 'start_time', 'end_time', 'notes'])]
class Shift extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /** Hora de inicio como "H:i", para mostrar y para agrupar por franja. */
    public function startLabel(): string
    {
        return substr($this->start_time, 0, 5);
    }

    public function endLabel(): string
    {
        return substr($this->end_time, 0, 5);
    }

    /** "10:30 a 19:30" -- clave usada para agrupar turnos con el mismo horario en una sola fila. */
    public function rangeLabel(): string
    {
        return $this->startLabel().' a '.$this->endLabel();
    }

    /**
     * El turno cruza medianoche cuando la hora de término es menor o igual
     * a la de inicio (ej. 22:30 a 08:30) -- no se guardan dos filas, se
     * interpreta que termina al día siguiente.
     */
    public function crossesMidnight(): bool
    {
        return $this->end_time <= $this->start_time;
    }

    public function startsAt(): Carbon
    {
        return Carbon::parse($this->date->toDateString().' '.$this->start_time, 'America/Santiago');
    }

    public function endsAt(): Carbon
    {
        $ends = Carbon::parse($this->date->toDateString().' '.$this->end_time, 'America/Santiago');

        return $this->crossesMidnight() ? $ends->addDay() : $ends;
    }

    public function durationHours(): float
    {
        return $this->startsAt()->diffInMinutes($this->endsAt()) / 60;
    }
}
