<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Datos reales de la cuenta para el mensaje de WhatsApp de "instrucciones
    // de pago" (ver reservations/show.blade.php) -- sin esto configurado, ese
    // mensaje NO incluye datos de transferencia (para no mandarle a un
    // cliente una cuenta inventada o de otro negocio).
    'hh_payment' => [
        'bank_titular' => env('HH_BANK_TITULAR'),
        'bank_rut' => env('HH_BANK_RUT'),
        'bank_name' => env('HH_BANK_NAME'),
        'bank_account_type' => env('HH_BANK_ACCOUNT_TYPE'),
        'bank_account_number' => env('HH_BANK_ACCOUNT_NUMBER'),
        'bank_email' => env('HH_BANK_EMAIL'),
        'mercadopago_link' => env('HH_MERCADOPAGO_LINK'),
    ],

];
