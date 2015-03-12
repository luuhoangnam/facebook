<?php

return [
    'app_id'           => env('FACEBOOK_APP_ID'),
    'app_secret'       => env('FACEBOOK_APP_SECRET'),
    'app_redirect_url' => env('FACEBOOK_REDIRECT_URL'),
    'connections'      => [
        'neo4j' => [
            'driver'   => 'neo4j',
            'host'     => env('NEO4J_HOST', 'localhost'),
            'port'     => env('NEO4J_PORT', '7474'),
            'username' => env('NEO4J_USERNAME'),
            'password' => env('NEO4J_PASSWORD'),
            'https'    => env('NEO4J_HTTPS', false),
        ],
    ],
];
