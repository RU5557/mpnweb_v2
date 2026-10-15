<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Admin Credentials for MPNWEB Panel
    |--------------------------------------------------------------------------
    | Value diambil dari .env, TIDAK ada fallback 'admin' di sini agar aman.
    | Jika .env belum di-set, aplikasi akan throw error 500, bukan bisa
    | login pakai admin/admin.
    |
    | Pastikan di .env kamu set:
    | ADMIN_USERNAME=...
    | ADMIN_PASSWORD=...
    */
    'username' => env('ADMIN_USERNAME'),
    'password' => env('ADMIN_PASSWORD'),
    // Nomor WA Admin untuk error handling - format internasional tanpa +/0
    // Contoh: 628123456789
    'wa_number' => env('ADMIN_WA_NUMBER', '628123456789'),
];
