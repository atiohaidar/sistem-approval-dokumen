<?php

return [
    // Apply CORS to API and Sanctum CSRF cookie route (and optionally login/logout if using web routes)
    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    // Allow local frontend origins; override via FRONTEND_URL if set
    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'http://127.0.0.1:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Required for Sanctum cookie-based SPA auth
    'supports_credentials' => true,
];
