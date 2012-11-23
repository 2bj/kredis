<?php defined('SYSPATH') or die('No direct access allowed.');

return [
    'default' => [
        'connection' => [
            'hostname'   => '127.0.0.1',
            'port'       => 6379,
            // 'timeout'    => 0,
        ],
        // 'database'   => 0,
        // 'password'   => 'foobared',
        'persistent' => FALSE,
        'prefix'     => 'app:',
        'serializer' => 'none', // none - Redis::SERIALIZER_NONE, php - Redis::SERIALIZER_PHP, igbinary - Redis::SERIALIZER_IGBINARY
    ],
    'cache'   => [
        'connection' => '/usr/local/var/run/redis.sock', // <-- via socket
        'prefix'     => 'cache:',
        'serializer' => 'php'
    ]
];