<?php

return [

    // Use the Redis connection name defined in config/database.php['redis']
    'use' => env('HORIZON_USE', 'default'),

    // Horizon dashboard path
    'path' => env('HORIZON_PATH', 'horizon'),

    // Prefix for Horizon's Redis keys (useful for multi-env)
    'prefix' => env('HORIZON_PREFIX', 'horizon:'),

    // Horizon should use the default Redis connection unless overridden
    'middleware' => ['web'],

    'waits' => [
        'redis:default' => 60,
        'redis:sms' => 60,
    ],

    'defaults' => [
        'supervisor' => [
            'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
            'queue' => explode(',', env('HORIZON_QUEUES', env('REDIS_QUEUE', 'default'))),
            'balance' => env('HORIZON_BALANCE', 'auto'),
            'maxProcesses' => (int) env('HORIZON_MAX_PROCESSES', 10),
            'minProcesses' => (int) env('HORIZON_MIN_PROCESSES', 1),
            'tries' => (int) env('HORIZON_TRIES', 3),
            'nice' => 0,
        ],
    ],

    'environments' => [
        'production' => [
            'supervisor-1' => [
                'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
                'queue' => explode(',', env('HORIZON_QUEUES', env('REDIS_QUEUE', 'default'))),
                'balance' => env('HORIZON_BALANCE', 'auto'),
                'maxProcesses' => (int) env('HORIZON_MAX_PROCESSES', 20),
                'minProcesses' => (int) env('HORIZON_MIN_PROCESSES', 2),
                'memory' => (int) env('HORIZON_MEMORY_LIMIT', 128),
                'tries' => (int) env('HORIZON_TRIES', 3),
                'timeout' => (int) env('HORIZON_TIMEOUT', 90),
                'nice' => 0,
            ],
        ],

        'local' => [
            'supervisor-1' => [
                'connection' => env('REDIS_QUEUE_CONNECTION', 'default'),
                'queue' => explode(',', env('HORIZON_QUEUES', env('REDIS_QUEUE', 'default'))),
                'balance' => 'simple',
                'maxProcesses' => 5,
                'minProcesses' => 1,
                'tries' => 1,
            ],
        ],
    ],
];


