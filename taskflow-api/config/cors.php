<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi CORS untuk TaskFlow API. Hanya izinkan origin dari domain
    | frontend yang terdaftar (bukan wildcard *) untuk keamanan production.
    |
    | Set FRONTEND_URL di .env (lokal) dan di environment variable Railway
    | (production) ke URL Vercel kamu, contoh: https://taskflow.vercel.app
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_filter(
        explode(',', env('FRONTEND_URL', 'http://localhost:5173'))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 86400,

    'supports_credentials' => false,

];
