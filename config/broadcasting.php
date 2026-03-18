<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "pusher", "ably", "redis", "log", "null"
    |
    */

    /*
    |--------------------------------------------------------------------------
    | bankOS Real-time Configuration
    |--------------------------------------------------------------------------
    | To enable real-time features (transaction alerts, AML compliance dashboard):
    |
    | Option A — Laravel Reverb (self-hosted WebSocket server, recommended):
    |   1. php artisan reverb:install
    |   2. Set BROADCAST_DRIVER=reverb in .env
    |   3. Start: php artisan reverb:start
    |
    | Option B — Pusher (managed cloud WebSocket):
    |   1. Set BROADCAST_DRIVER=pusher in .env
    |   2. Set PUSHER_APP_KEY, PUSHER_APP_SECRET, PUSHER_APP_ID, PUSHER_APP_CLUSTER in .env
    |
    | Frontend setup (both options):
    |   npm install laravel-echo pusher-js
    |   Configure Echo in resources/js/bootstrap.js
    |
    | Currently defaults to 'log' driver for development (events are logged, not broadcast).
    */

    'default' => env('BROADCAST_DRIVER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over websockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
