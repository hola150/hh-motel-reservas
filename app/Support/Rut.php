<?php

namespace App\Support;

/**
 * Valida y formatea RUT chileno (dígito verificador módulo 11). Se usa al
 * cargar el documento del cliente en la reserva — un RUT mal tipeado no
 * debería quedar guardado como si fuera válido.
 */
class Rut
{
    /**
     * Normaliza a "12345678-9" (sin puntos, con guión, dígito verificador
     * en mayúscula si es K).
     */
    public static function normalize(string $raw): string
    {
        $clean = strtoupper(preg_replace('/[^0-9kK]/', '', $raw));
        if ($clean === '') {
            return '';
        }

        $body = substr($clean, 0, -1);
        $dv = substr($clean, -1);

        return $body === '' ? $dv : $body.'-'.$dv;
    }

    /**
     * Dígito verificador módulo 11 sobre el cuerpo del RUT (sin el propio DV).
     */
    public static function computeDv(string $body): string
    {
        $sum = 0;
        $multiplier = 2;

        for ($i = strlen($body) - 1; $i >= 0; $i--) {
            $sum += ((int) $body[$i]) * $multiplier;
            $multiplier = $multiplier === 7 ? 2 : $multiplier + 1;
        }

        $remainder = 11 - ($sum % 11);

        return match ($remainder) {
            11 => '0',
            10 => 'K',
            default => (string) $remainder,
        };
    }

    /**
     * ¿El RUT completo (cuerpo + dígito verificador) es válido? Exige al
     * menos 7 dígitos en el cuerpo — RUTs más cortos no existen en la
     * práctica y suelen ser errores de tipeo.
     */
    public static function isValid(string $raw): bool
    {
        $normalized = self::normalize($raw);
        if (! str_contains($normalized, '-')) {
            return false;
        }

        [$body, $dv] = explode('-', $normalized);
        if (strlen($body) < 7 || ! ctype_digit($body)) {
            return false;
        }

        return self::computeDv($body) === $dv;
    }
}
