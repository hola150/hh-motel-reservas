<?php

namespace App\Support;

/**
 * Normaliza un teléfono a E.164 chileno (+56XXXXXXXXX). El número de
 * WhatsApp es la identidad del cliente — misma normalización en todos lados
 * (crear reserva, buscar cliente, sync a GHL) para que siempre matchee.
 */
class Phone
{
    public static function toE164(string $raw): string
    {
        $digits = preg_replace('/\D/', '', $raw);

        // 9 dígitos sin prefijo país => celular chileno, se antepone 56.
        if (! str_starts_with($digits, '56') && strlen($digits) === 9) {
            $digits = '56'.$digits;
        }

        return '+'.$digits;
    }

    /**
     * ¿Tiene pinta de número completo? Se usa para no disparar la búsqueda
     * mientras todavía están tipeando/pegando.
     */
    public static function looksComplete(string $raw): bool
    {
        return strlen(preg_replace('/\D/', '', $raw)) >= 9;
    }
}
