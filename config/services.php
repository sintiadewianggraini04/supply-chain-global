<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),

        'secret' => env(
            'AWS_SECRET_ACCESS_KEY'
        ),

        'region' => env(
            'AWS_DEFAULT_REGION',
            'us-east-1'
        ),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env(
                'SLACK_BOT_USER_OAUTH_TOKEN'
            ),

            'channel' => env(
                'SLACK_BOT_USER_DEFAULT_CHANNEL'
            ),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | REST Countries
    |--------------------------------------------------------------------------
    */

    'rest_countries' => [
        'base_url' => env(
            'REST_COUNTRIES_BASE_URL',
            'https://restcountries.com/v3.1'
        ),

        'timeout' => (int) env(
            'REST_COUNTRIES_TIMEOUT',
            15
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | GNews
    |--------------------------------------------------------------------------
    */

    'gnews' => [
        'api_key' => env('GNEWS_API_KEY'),

        'base_url' => env(
            'GNEWS_BASE_URL',
            'https://gnews.io/api/v4'
        ),

        'language' => env(
            'GNEWS_LANGUAGE',
            'en'
        ),

        'country' => env(
            'GNEWS_COUNTRY',
            ''
        ),

        'timeout' => (int) env(
            'GNEWS_TIMEOUT',
            15
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Open-Meteo
    |--------------------------------------------------------------------------
    */

    'open_meteo' => [
        'base_url' => env(
            'OPEN_METEO_BASE_URL',
            'https://api.open-meteo.com/v1'
        ),

        'timeout' => (int) env(
            'OPEN_METEO_TIMEOUT',
            12
        ),

        'cache_minutes' => (int) env(
            'OPEN_METEO_CACHE_MINUTES',
            30
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | World Bank
    |--------------------------------------------------------------------------
    */

    'world_bank' => [
        'base_url' => env(
            'WORLD_BANK_BASE_URL',
            'https://api.worldbank.org/v2'
        ),

        'timeout' => (int) env(
            'WORLD_BANK_TIMEOUT',
            12
        ),

        'cache_minutes' => (int) env(
            'WORLD_BANK_CACHE_MINUTES',
            720
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | ExchangeRate-API Open Access
    |--------------------------------------------------------------------------
    |
    | Gratis, tanpa API key.
    | Endpoint:
    | https://open.er-api.com/v6/latest/USD
    |
    */

    'exchange_rate' => [
        'base_url' => env(
            'EXCHANGE_RATE_BASE_URL',
            'https://open.er-api.com/v6'
        ),

        'cache_minutes' => (int) env(
            'EXCHANGE_RATE_CACHE_MINUTES',
            60
        ),

        'timeout' => (int) env(
            'EXCHANGE_RATE_TIMEOUT',
            12
        ),
    ],

];