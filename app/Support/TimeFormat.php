<?php

namespace App\Support;

class TimeFormat
{
    /**
     * Minutos a "Xh Ymin" (o "X min" si es menos de una hora). Usa el valor
     * absoluto — quien llama decide si antepone "hace" o "en".
     */
    public static function minutes(int $minutes): string
    {
        $abs = abs($minutes);

        if ($abs < 60) {
            return "{$abs} min";
        }

        $hours = intdiv($abs, 60);
        $mins = $abs % 60;

        return $mins > 0 ? "{$hours}h {$mins}min" : "{$hours}h";
    }
}
