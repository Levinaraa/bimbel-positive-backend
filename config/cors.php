<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // UBAH 1: Izinkan semua domain (Bintang)
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // UBAH 2: Set FALSE agar bisa pakai Bintang (*) di atas
    // Karena login Anda pakai Token (Bearer), ini aman diset false.
    'supports_credentials' => false,

];