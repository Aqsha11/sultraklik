<?php

namespace Tests\Feature;

use App\Http\Middleware\TrustProxies;
use App\Providers\AppServiceProvider;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Situs dilayani lewat Cloudflare, jadi Laravel menerima request dari IP edge
 * Cloudflare, bukan IP pengunjung. Test ini mengunci dua hal yang mudah salah:
 *
 *   1. Di belakang Cloudflare, IP asli dan skema https harus terbaca.
 *   2. Kalau request datang dari IP yang bukan Cloudflare, X-Forwarded-For
 *      harus diabaikan total. Kalau tidak, siapa pun bisa memalsukan IP untuk
 *      melewati rate limit komentar, like, login, dan pembatasan IP.
 */
class TrustedProxyTest extends TestCase
{
    use RefreshDatabase;

    /** Masuk ke rentang resmi Cloudflare 103.21.244.0/22. */
    private const CLOUDFLARE_IP = '103.21.244.5';

    protected function setUp(): void
    {
        parent::setUp();

        // config adalah sumber tunggal daftar proxy; set di sini supaya test
        // tidak bergantung pada isi .env atau cache konfigurasi mesin ini.
        config(['trustedproxy.proxies' => [self::CLOUDFLARE_IP]]);
    }

    /**
     * Bangun request, jalankan middleware, lalu kembalikan request yang sudah
     * diproses supaya bisa diperiksa.
     */
    private function resolve(array $server): Request
    {
        $request = Request::create('http://localhost/', 'GET', [], [], [], $server);

        app(TrustProxies::class)->handle($request, fn ($passed) => $passed);

        return $request;
    }

    public function test_client_ip_and_https_are_read_from_cloudflare_headers(): void
    {
        $request = $this->resolve([
            'REMOTE_ADDR' => self::CLOUDFLARE_IP,
            'HTTP_X_FORWARDED_FOR' => '114.114.114.114',
            'HTTP_X_FORWARDED_PROTO' => 'https',
            'HTTP_X_FORWARDED_HOST' => 'sultraklik.id',
        ]);

        $this->assertSame('114.114.114.114', $request->ip());
        $this->assertTrue($request->isSecure());
        $this->assertSame('https', $request->getScheme());
        $this->assertSame('sultraklik.id', $request->getHost());
    }

    public function test_forwarded_ip_is_ignored_when_the_peer_is_not_cloudflare(): void
    {
        // 203.0.113.9 adalah TEST-NET-3, bukan rentang Cloudflare.
        $request = $this->resolve([
            'REMOTE_ADDR' => '203.0.113.9',
            'HTTP_X_FORWARDED_FOR' => '114.114.114.114',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $this->assertSame('203.0.113.9', $request->ip());
        $this->assertFalse($request->isSecure());
    }

    public function test_spoofed_entries_are_discarded_in_favour_of_the_real_client_ip(): void
    {
        // Penyerang melempar X-Forwarded-For palsu ke Cloudflare. Cloudflare
        // menambahkan IP asli yang ia lihat di sebelah kanan, lalu Symfony
        // membaca rantai dari kanan ke kiri dan mengambil entri paling kanan
        // yang bukan proxy. Jadi angka pilihan penyerang terbuang dan yang
        // dipakai adalah IP asli.
        $request = $this->resolve([
            'REMOTE_ADDR' => self::CLOUDFLARE_IP,
            'HTTP_X_FORWARDED_FOR' => '9.9.9.9, 8.8.8.8, 114.114.114.114',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $this->assertSame('114.114.114.114', $request->ip());
    }

    public function test_intermediate_cloudflare_hops_are_skipped(): void
    {
        // Rantai umum: [<client>, <edge cloudflare lain>, <edge cloudflare>].
        // Proxy di tengah ikut dibuang karena ada di daftar tepercaya.
        config(['trustedproxy.proxies' => [self::CLOUDFLARE_IP, '104.16.0.1']]);

        $request = $this->resolve([
            'REMOTE_ADDR' => self::CLOUDFLARE_IP,
            'HTTP_X_FORWARDED_FOR' => '114.114.114.114, 104.16.0.1',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $this->assertSame('114.114.114.114', $request->ip());
    }

    public function test_only_the_cloudflare_ranges_are_removed_from_the_chain(): void
    {
        // 10.0.0.7 bukan proxy tepercaya, jadi tidak boleh dibuang. Dengan
        // begitu, kalau daftar Cloudflare ikut berubah, test inilah yang
        // gagal lebih dulu dan memberi tahu, bukan diam-diam melonggarkan
        // kepercayaan ke seluruh internet.
        config(['trustedproxy.proxies' => [self::CLOUDFLARE_IP]]);

        $request = $this->resolve([
            'REMOTE_ADDR' => self::CLOUDFLARE_IP,
            'HTTP_X_FORWARDED_FOR' => '114.114.114.114, 10.0.0.7',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $this->assertSame('10.0.0.7', $request->ip());
    }

    public function test_no_proxy_is_trusted_when_the_list_is_empty(): void
    {
        config(['trustedproxy.proxies' => []]);

        $request = $this->resolve([
            'REMOTE_ADDR' => self::CLOUDFLARE_IP,
            'HTTP_X_FORWARDED_FOR' => '114.114.114.114',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);

        $this->assertSame(self::CLOUDFLARE_IP, $request->ip());
        $this->assertFalse($request->isSecure());
    }

    public function test_trust_proxies_runs_before_the_rest_of_the_stack(): void
    {
        $global = $this->app->make(Kernel::class)->getGlobalMiddleware();

        $this->assertContains(TrustProxies::class, $global);
        // Kalau posisinya di belakang, middleware lain sudah membaca
        // request->ip() sebagai IP Cloudflare sebelum proxy dipercaya.
        $this->assertSame(TrustProxies::class, array_values($global)[0]);
    }

    public function test_session_cookie_is_secure_when_app_url_is_https(): void
    {
        config(['app.url' => 'https://sultraklik.id']);

        (new AppServiceProvider($this->app))->boot();

        $this->assertTrue(config('session.secure'));
        $this->assertTrue(config('session.http_only'));
        // "strict" akan membuang cookie saat Cloudflare mengarahkan user balik
        // ke aplikasi setelah OTP, sehingga orang terjebak di loop login.
        $this->assertSame('lax', config('session.same_site'));
    }
}
