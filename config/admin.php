<?php

namespace App\Providers;

return [
    'name' => env('ADMIN_NAME', 'System Admin'),
    'email' => env('ADMIN_EMAIL', 'admin@example.com'),
    'password' => env('ADMIN_PASSWORD', '12345678'),
];
