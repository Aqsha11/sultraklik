<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Trusted Proxies
|--------------------------------------------------------------------------
|
| Situs ini dilayani lewat Cloudflare, jadi request yang sampai ke Laravel
| berasal dari IP edge Cloudflare — bukan IP pengunjung. Tanpa TrustProxies:
|
|   - Request::ip() mengembalikan IP Cloudflare, sehingga rate limit komentar,
|     like, dan login akan menghitung semua pengunjung sebagai satu IP.
|   - Request::isSecure() selalu false walau aslinya HTTPS, sehingga cookie
|     sesi tidak mendapat flag "secure".
|   - URL hasil generate() memakai http:// dan host yang salah.
|
| Daftar di bawah adalah rentang IP resmi Cloudflare (www.cloudflare.com/ips-v4
| dan /ips-v6). Hanya rentang ini yang dipercaya, sehingga X-Forwarded-For
| milik orang yang mencoba sneak past origin tetap diabaikan.
|
| Kalau situs diakses langsung tanpa Cloudflare (mis. development lokal),
| kosongkan nilainya: TrustProxies akan dilewati.
|
*/

return [

    /*
    | Kosongkan nilai ini kalau aplikasi tidak memakai reverse proxy sama
    | sekali. Kosong = tidak ada proxy yang dipercaya, dan request dari
    | Cloudflare akan terlihat seperti request biasa (ip = IP edge, bukan
    | IP asli pengunjung).
    */
    'proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),

    /*
    | Header yang dibaca Symfony untuk mempercayai nilai dari proxy.
    | Sama persis dengan default Laravel; ditulis ulang supaya jelas.
    |
    | Rantai X-Forwarded-For dibaca dari kanan ke kiri: Symfony membuang entri
    | mana pun yang ada di daftar proxy tepercaya, lalu mengembalikan sisanya
    | dalam urutan terbalik. Karena Cloudflare MENAMBAHKAN IP asli yang ia lihat
    | di ujung kanan, entri paling kanan yang bukan proxy itulah IP asli
    | pengunjung. Nilai yang dipalsukan di ujung kiri terbuang.
    |
    | Verifikasi: tests/Feature/TrustedProxyTest.php
    */
    'headers' => Request::HEADER_X_FORWARDED_FOR
        | Request::HEADER_X_FORWARDED_HOST
        | Request::HEADER_X_FORWARDED_PORT
        | Request::HEADER_X_FORWARDED_PROTO
        | Request::HEADER_X_FORWARDED_PREFIX
        | Request::HEADER_X_FORWARDED_AWS_ELB,

];
