<?php

return [

    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
    'passkey' => env('MPESA_PASSKEY'),
    'shortcode' => env('MPESA_SHORTCODE', '174379'),
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    // Sandbox only: accept any valid Kenyan number and complete payment for demos.
    // Real STK prompts on any phone require MPESA_ENVIRONMENT=production (Go Live).
    'demo_any_phone' => env('MPESA_DEMO_ANY_PHONE', false),

    'base_url' => env('MPESA_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke',

];
