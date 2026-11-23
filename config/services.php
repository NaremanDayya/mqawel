<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
    ],

    // Thawani payment gateway (subscriptions). THAWANI_MODE is "test" (default) or "live";
    // the keys come from the Thawani dashboard (test keys for test, live keys for live).
    'thawani' => [
        'mode' => env('THAWANI_MODE', 'test'),
        'secret_key' => env('THAWANI_SECRET_KEY'),
        'publishable_key' => env('THAWANI_PUBLISHABLE_KEY'),
    ],

    'whatsapp_internal' => [
        'token' => env('WHATSAPP_INTERNAL_TOKEN'),
    ],

    // Mqawel+ as a client of the multi-tenant WhatsApp platform: it pushes its people and
    // projects there (api_key) and executes the actions the platform sends back, which are
    // signed with callback_secret.
    'whatsapp_platform' => [
        'base_url' => env('WHATSAPP_PLATFORM_BASE_URL'),
        'api_key' => env('WHATSAPP_PLATFORM_API_KEY'),
        'callback_secret' => env('WHATSAPP_PLATFORM_CALLBACK_SECRET'),
    ],

];
