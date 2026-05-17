<?php

return [
    'host' => env('ROUTEROS_HOST', '192.168.88.1'),
    'username' => env('ROUTEROS_USERNAME', 'admin'),
    'password' => env('ROUTEROS_PASSWORD', ''),
    'port' => (int) env('ROUTEROS_PORT', 8728),
    'timeout' => (int) env('ROUTEROS_TIMEOUT', 10),
    'reconnect_attempts' => (int) env('ROUTEROS_RECONNECT_ATTEMPTS', 1),

    'proxy' => [
        'enabled' => env('ROUTEROS_PROXY_ENABLED', false),
        'type' => env('ROUTEROS_PROXY_TYPE', 'socks5'), // socks5 or http
        'host' => env('ROUTEROS_PROXY_HOST', '127.0.0.1'),
        'port' => (int) env('ROUTEROS_PROXY_PORT', 1080),
        'username' => env('ROUTEROS_PROXY_USERNAME'),
        'password' => env('ROUTEROS_PROXY_PASSWORD'),
    ],

    'ssl' => [
        'enabled' => env('ROUTEROS_SSL_ENABLED', false),
        'verify_peer' => env('ROUTEROS_SSL_VERIFY_PEER', false),
        'verify_peer_name' => env('ROUTEROS_SSL_VERIFY_PEER_NAME', false),
        'allow_self_signed' => env('ROUTEROS_SSL_ALLOW_SELF_SIGNED', true),
    ],
];
