<?php

return [

    'store_name' => env('STORE_NAME', 'SARAKI'),

    // ISO 4217 code. Paystack amounts are always sent as amount × 100 (GHS pesewas; XOF also × 100).
    'currency' => env('STORE_CURRENCY', 'GHS'),

    'currency_locale' => [
        'fr' => env('STORE_CURRENCY_LOCALE_FR', 'fr_GH'),
        'en' => env('STORE_CURRENCY_LOCALE_EN', 'en_GH'),
    ],

    'supported_locales' => ['fr', 'en'],

    'order_number_prefix' => env('ORDER_NUMBER_PREFIX', 'SRK'),

    'order_expiry_minutes' => (int) env('ORDER_EXPIRY_MINUTES', 30),

    'admin_email' => env('STORE_ADMIN_EMAIL', 'admin@saraki.test'),

];
