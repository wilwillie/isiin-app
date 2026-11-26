<?php

return [
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Asia/Jakarta'),

    // Komisi Isiin dari total biaya layanan (sewa, pengiriman, pemasangan).
    'commission_rate' => 0.10,

    'room_types' => [
        'ruang_tamu'  => 'Ruang Tamu',
        'kamar_utama' => 'Kamar Utama',
        'rumah_penuh' => 'Rumah Penuh',
    ],

    'styles' => ['Modern Minimalist', 'Scandinavian', 'Japandi', 'Industrial'],
];
