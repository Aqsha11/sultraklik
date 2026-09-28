<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Verifikasi token Cloudflare Turnstile.
 *
 * Turnstile hanya aktif kalau site key dan secret key sama-sama terisi, jadi
 * development lokal dan test tidak perlu key. Verifikasi selalu gagal tertutup
 * (fail closed): kalau token hilang atau Cloudflare tidak bisa dihubungi,
 * request ditolak — bukan diterima dengan hopes.
 */
final class Turnstile
{
    /**
     * Nama input yang diisi widget Turnstile di browser.
     */
    public const FIELD = 'cf-turnstile-response';

    /**
     * Action yang membedakan satu widget dari yang lain.
     */
    public const ACTION_LOGIN = 'login';

    public const ACTION_COMMENT = 'comment';

    /**
     * Apakah captcha aktif. Butuh kedua key dan tidak dinonaktifkan eksplisit.
     */
    public static function enabled(): bool
    {
        // Pakai loose check, bukan === false: config/turnstile.php sudah
        // memfilter nilai .env jadi boolean, tapi loosely-typed check di sini
        // tetap aman kalau config di-override manual (mis. lewat test).
        if (! config('turnstile.enabled')) {
            return false;
        }

        return filled(config('turnstile.site_key'))
            && filled(config('turnstile.secret_key'));
    }

    /**
     * Public site key, aman dikirim ke view. Null saat captcha nonaktif.
     */
    public static function siteKey(): ?string
    {
        return self::enabled() ? (string) config('turnstile.site_key') : null;
    }

    public static function scriptUrl(): string
    {
        return (string) config('turnstile.script_url');
    }

    /**
     * Periksa token milik request ini.
     *
     * @param  string  $action  action yang harus cocok dengan widget, kosongkan untuk tidak diperiksa
     * @param  string|null  $token  token dibaca manual; dipakai halaman login Filament karena
     *                              token-nya ada di state Livewire, bukan di body request
     */
    public static function verify(Request $request, string $action = '', ?string $token = null): bool
    {
        // Nonaktif: tidak ada yang perlu diverifikasi, dan ini yang membuat
        // test serta development lokal tetap bisa jalan.
        if (! self::enabled()) {
            return true;
        }

        $token ??= $request->input(self::FIELD);

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('turnstile.timeout', 5))
                ->post((string) config('turnstile.verify_url'), [
                    'secret' => (string) config('turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $request->ip(),
                ]);
        } catch (ConnectionException $e) {
            // Jangan gagal terbuka: request bot tidak boleh lolos hanya
            // karena Cloudflare sedang tidak bisa dihubungi.
            Log::warning('Turnstile siteverify gagal dijangkau: '.$e->getMessage());

            return false;
        }

        if (! $response->successful()) {
            Log::warning('Turnstile siteverify mengembalikan HTTP '.$response->status().'.');

            return false;
        }

        $payload = $response->json();

        if (! is_array($payload) || ($payload['success'] ?? false) !== true) {
            Log::notice('Turnstile menolak token. Kode: '.json_encode($payload['error-codes'] ?? []));

            return false;
        }

        // Action dikirim sebagai data-action di widget, jadi kita yang menentukan
        // nilainya dan tidak akan pernah tidak cocok kecuali kalau ada manipulasi.
        if ($action !== '' && ($payload['action'] ?? null) !== $action) {
            Log::notice('Turnstile action tidak cocok: '.($payload['action'] ?? 'kosong'));

            return false;
        }

        $hostnames = (array) config('turnstile.hostnames', []);

        if ($hostnames !== [] && ! in_array($payload['hostname'] ?? null, $hostnames, true)) {
            Log::warning('Turnstile hostname tidak dikenal: '.($payload['hostname'] ?? 'kosong'));

            return false;
        }

        return true;
    }
}
