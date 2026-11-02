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

    // ИИ-помощник (поиск, тексты объявлений, проверка для модератора). Без ключа функции ИИ просто недоступны
    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT'),
        // путь к cacert.pem, если PHP не видит системные сертификаты (Windows); пусто — стандартная проверка
        'ca_bundle' => env('OPENAI_CA_BUNDLE'),
        // модель, которая видит картинки (проверка фото объявлений); пусто — проверка фото выключена
        'vision_model' => env('OPENAI_VISION_MODEL'),
    ],

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

    'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    // Sign in with Apple: client_id — это Services ID (например, tj.domly.web) из Apple Developer
    'apple' => [
        'client_id' => env('APPLE_CLIENT_ID'),
        'redirect' => env('APPLE_REDIRECT_URI'),
    ],


];
