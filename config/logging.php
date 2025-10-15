<?php

use Monolog\Handler\StreamHandler;

return [

    // Canal por defecto: stderr (ideal para Render)
    'default' => env('LOG_CHANNEL', 'stderr'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    'channels' => [

        // El stack SOLO usa stderr (no 'single')
        'stack' => [
            'driver' => 'stack',
            'channels' => ['stderr'],
            'ignore_exceptions' => false,
        ],

        // STDERR a través de Monolog
        'stderr' => [
            'driver'   => 'monolog',
            'handler'  => StreamHandler::class,
            'with'     => ['stream' => 'php://stderr'],
            'level'    => env('LOG_LEVEL', 'debug'),
        ],

        // Los dejo definidos por si algún día quieres archivos,
        // pero NO se usan en el stack.
        'single' => [
            'driver' => 'single',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 14,
        ],

        'null' => [
            'driver' => 'null',
        ],
    ],
];
