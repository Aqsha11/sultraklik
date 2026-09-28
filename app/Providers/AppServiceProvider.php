<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->isProduction()) {
            config(['app.debug' => false]);
        }

        if (str_starts_with((string) config('app.url'), 'https://')) {
            config([
                'session.secure' => true,
                'session.http_only' => true,
                'session.same_site' => 'lax',
            ]);
        }

        $this->warnAboutDummyTurnstileKeys();
    }

    /**
     * Test key Turnstile milik Cloudflare selalu lulus verifikasi, jadi captcha
     * tidak melindungi apa pun. Kalau .env lokal ikut ter-deploy ke server,
     * ini harus diketahui.
     *
     * Sengaja hanya memberi peringatan, bukan exception: CAPTCHA salah
     * konfigurasi tidak cukup serius untuk menjatuhkan seluruh situs berita.
     */
    private function warnAboutDummyTurnstileKeys(): void
    {
        if (! $this->app->isProduction() || ! config('turnstile.enabled')) {
            return;
        }

        $siteKey = (string) config('turnstile.site_key');
        $secretKey = (string) config('turnstile.secret_key');

        if (! str_starts_with($siteKey, '1x0000') || ! str_starts_with($secretKey, '1x0000')) {
            return;
        }

        // Dibatasi satu kali per jam supaya tidak membanjiri log produksi.
        if (! Cache::add('turnstile:dummy-keys-warned', true, 3600)) {
            return;
        }

        Log::critical(
            'Cloudflare Turnstile memakai TEST KEY di produksi. Widget akan selalu '
            .'lulus sehingga captcha tidak melindungi apa pun. Buat widget Turnstile '
            .'di dashboard Cloudflare lalu isi TURNSTILE_SITE_KEY dan TURNSTILE_SECRET_KEY '
            .'yang sebenarnya, lalu jalankan php artisan optimize:clear.',
            ['site_key' => $siteKey]
        );
    }
}
