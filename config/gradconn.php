<?php

return [
    'courses' => ['BSIS', 'BLIS', 'BSTM', 'BSHM', 'BSED Math', 'BSED Science', 'BSNED', 'BPA'],
    'admin_seed' => [
        'name' => env('ADMIN_SEED_NAME', 'GradConn Administrator'),
        'username' => env('ADMIN_SEED_USERNAME', 'admin'),
        'email' => env('ADMIN_SEED_EMAIL', 'admin@gradconn.local'),
        'password' => env('ADMIN_SEED_PASSWORD'),
    ],
    'alumni_officer_seed' => [
        'name' => env('ALUMNI_OFFICER_SEED_NAME', 'GradConn Alumni Officer'),
        'username' => env('ALUMNI_OFFICER_SEED_USERNAME', 'alumni_officer'),
        'email' => env('ALUMNI_OFFICER_SEED_EMAIL', 'alumni-officer@gradconn.local'),
        'password' => env('ALUMNI_OFFICER_SEED_PASSWORD'),
    ],
];
