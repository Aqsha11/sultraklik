<?php

/*
|--------------------------------------------------------------------------
| Cloudflare Turnstile
|--------------------------------------------------------------------------
|
| Captcha untuk form login admin dan form komentar. Widget-nya gratis dan
| tidak menampilkan challenge gambar puzzle seperti reCAPTCHA.
|
| Turnstile HANYA aktif kalau site key dan secret key sama-sama terisi, dan
| TURNSTILE_ENABLED tidak bernilai false. Jadi development lokal dan test
| otomatis tidak perlu key sama sekali, dan website tidak pernah rusak
| hanya karena lupa mengisi key.
|
| Kalau widget gagal dimuat (CSP, ad blocker, atau user memblokir
| challenges.cloudflare.com), login akan ditolak. Matikan dengan
| TURNSTILE_ENABLED=false di .env kalau perlu akses darurat.
|
| Site key aman dipublikasikan ke halaman; secret key TIDAK PERNAH boleh
| keluar ke view.
|
| Dokumentasi: https://developers.cloudflare.com/turnstile/
|
*/

return [

    'enabled' => env('TURNSTILE_ENABLED', true),

    'site_key' => env('TURNSTILE_SITE_KEY'),

    'secret_key' => env('TURNSTILE_SECRET_KEY'),

    'verify_url' => env('TURNSTILE_VERIFY_URL', 'https://challenges.cloudflare.com/turnstile/v0/siteverify'),

    'script_url' => env('TURNSTILE_SCRIPT_URL', 'https://challenges.cloudflare.com/turnstile/v0/api.js'),

    /*
    | Batas waktu menunggu jawaban Cloudflare. Jangan dibuat terlalu besar:
    | ini ada di jalur request login dan pengiriman komentar.
    */
    'timeout' => (int) env('TURNSTILE_TIMEOUT', 5),

    /*
    | Daftar hostname yang boleh mengirim token, Kosong = tidak diperiksa.
    |
    | Hostname di dalam token sudah terikat ke site key kita, jadi token dari
    | situs lain tidak akan lolos verifikasi secret key kita. Karena itu
    | pemeriksaan ini opsional: menambahkannya bisa mengunci admin keluar
    | kalau sultraklik.id dan www.sultraklik.id beda di Cloudflare.
    | Kalau nanti diaktifkan, isi keduanya:
    |
    |   'hostnames' => ['sultraklik.id', 'www.sultraklik.id'],
    */
    'hostnames' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TURNSTILE_HOSTNAMES', ''))
    ))),

];
