<?php

return [
    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway Configuration
    |--------------------------------------------------------------------------
    | Konfigurasi untuk koneksi ke WhatsApp Baileys Gateway.
    */

    'gateway_url' => env('WA_GATEWAY_URL', 'http://127.0.0.1:3001'),
    'gateway_secret' => env('WA_GATEWAY_SECRET', ''),

    /*
    | Timeout (detik) untuk HTTP request ke gateway.
    | Jangan pakai 0 (tak terbatas) karena bisa memblokir PHP worker.
    */
    'timeout' => (int) env('WA_GATEWAY_TIMEOUT', 30),

    /*
    | Delay antar pengiriman pesan (ms) untuk menghindari banned.
    */
    'send_delay' => (int) env('WA_SEND_DELAY', 1500),
];
