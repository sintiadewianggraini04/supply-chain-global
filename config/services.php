<?php

return [

'gnews' => [
    'base_url' => env(
        'GNEWS_BASE_URL',
        'https://gnews.io/api/v4/search'
    ),

    'api_key' => env('GNEWS_API_KEY'),

    'default_query' => env(
        'GNEWS_DEFAULT_QUERY',
        'logistics OR trade OR shipping OR economy'
    ),
],

    'rest_countries' => [
        'base_url' => env(
            'REST_COUNTRIES_BASE_URL',
            'https://api.restcountries.com/countries/v5'
        ),

        'api_key' => env('REST_COUNTRIES_API_KEY'),
    ],

'exchange_rate' => [
    'base_url' => env(
        'EXCHANGE_RATE_BASE_URL',
        'https://open.er-api.com/v6/latest'
    ),

    'default_base' => env('EXCHANGE_RATE_DEFAULT_BASE', 'USD'),
],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];