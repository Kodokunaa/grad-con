<?php

return [
    'courses' => ['BSIS', 'BSTM', 'BSHM', 'BSED Math', 'BSED Science', 'BSNED', 'BPA'],
    'admin_seed' => [
        'name' => env('ADMIN_SEED_NAME', 'GradConn Administrator'),
        'username' => env('ADMIN_SEED_USERNAME', 'admin'),
        'email' => env('ADMIN_SEED_EMAIL', 'admin@gradconn.local'),
        'password' => env('ADMIN_SEED_PASSWORD'),
    ],
];
