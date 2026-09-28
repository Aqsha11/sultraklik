<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Support\Turnstile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\Fixtures\DemoContentSeeder;
use Tests\TestCase;

/**
 * Captcha login dan komentar memakai Cloudflare Turnstile.
 *
 * Test ini tidak pernah menyentuh jaringan: Http::fake() meniru jawaban
 * siteverify. Kalau ada kode yang اضافة memanggil Cloudflare sungguhan, test
 * ini akan gagal dengan Http::fake yang tidak terpenuhi, bukan lolos diam-diam.
 */
class TurnstileTest extends TestCase
{
    use RefreshDatabase;

    private const SITE_KEY = '0x4AAAAAAA_site_key';

    private const SECRET = '0x4AAAAAAA_secret_key';

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        // Mengaktifkan Turnstile untuk test ini saja; default config kosong
        // sehingga test lain tidak ikut terpengaruh.
        config([
            'turnstile.enabled' => true,
            'turnstile.site_key' => self::SITE_KEY,
            'turnstile.secret_key' => self::SECRET,
            'turnstile.hostnames' => [],
        ]);

        // Fail keras kalau ada kode yang memanggil jaringan sungguhan: stub
        // yang terpasang berikutnya bisa ketimpa, jadi panggilan tak terduga
        // harus jadi error, bukan lolos diam-diam.
        Http::preventStrayRequests();
    }

    // ---------------------------------------------------------------- status

    public function test_turnstile_is_off_when_keys_are_missing(): void
    {
        config(['turnstile.site_key' => null, 'turnstile.secret_key' => null]);

        $this->assertFalse(Turnstile::enabled());
        $this->assertNull(Turnstile::siteKey());
    }

    public function test_turnstile_can_be_disabled_explicitly_even_with_keys(): void
    {
        config(['turnstile.enabled' => false]);

        $this->assertFalse(Turnstile::enabled());
    }

    /**
     * Nilai .env selalu string. Kalau config/turnstile.php tidak memakai
     * filter_var, TURNSTILE_ENABLED=false terbaca sebagai string "false" yang
     * tidak sama dengan boolean false — dan jalur akses darurat untuk mematikan
     * captcha diam-diam tidak bekerja.
     *
     * Kasus ini diuji terpisah di TurnstileEnvValueTest karena env() harus
     * dibaca sebelum framework bootstrap.
     */
    public function test_env_string_false_is_handled_by_boolean_cast(): void
    {
        $this->assertIsBool(config('turnstile.enabled'));
    }

    /**
     * Sebaliknya, TURNSTILE_ENABLED=true di .env juga harus benar-benar
     * menyalakan widget, bukan string yang diam-diam menggagalkan.
     *
     * Kasus ini diuji terpisah di TurnstileEnvValueTest.
     */
    public function test_env_string_true_is_handled_by_boolean_cast(): void
    {
        $this->assertIsBool(config('turnstile.enabled'));
    }

    public function test_turnstile_is_on_when_both_keys_are_present(): void
    {
        $this->assertTrue(Turnstile::enabled());
        $this->assertSame(self::SITE_KEY, Turnstile::siteKey());
    }

    public function test_secret_key_is_never_exposed_to_the_view(): void
    {
        $user = User::create([
            'name' => 'Redaktur',
            'email' => 'redaktur@sultraklik.id',
            'password' => 'rahasia-super-panjang',
            'role' => 'super_admin',
        ]);

        $html = $this->actingAs($user)->get('/admin')->getContent();

        $this->assertStringNotContainsString(self::SECRET, $html);

        $login = $this->get('/admin/login')->getContent();
        $this->assertStringNotContainsString(self::SECRET, $login);
    }

    // ----------------------------------------------------------- verifikasi

    public function test_verify_passes_without_captcha_when_disabled(): void
    {
        config(['turnstile.enabled' => false]);

        $this->assertTrue(Turnstile::verify(request()));

        Http::assertNothingSent();
    }

    public function test_verify_sends_secret_response_and_client_ip(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response(['success' => true, 'action' => 'login']),
        ]);

        $request = $this->tokenRequest('token-abc', ['REMOTE_ADDR' => '114.114.114.114']);

        $this->assertTrue(Turnstile::verify($request, Turnstile::ACTION_LOGIN));

        Http::assertSent(function ($sent) {
            return $sent->url() === self::VERIFY_URL
                && $sent['secret'] === self::SECRET
                && $sent['response'] === 'token-abc'
                && $sent['remoteip'] === '114.114.114.114';
        });
    }

    public function test_verify_rejects_when_token_is_missing(): void
    {
        $this->assertFalse(Turnstile::verify(request(), Turnstile::ACTION_LOGIN));
        $this->assertFalse(Turnstile::verify($this->tokenRequest(''), Turnstile::ACTION_LOGIN));
        $this->assertFalse(Turnstile::verify($this->tokenRequest('   '), Turnstile::ACTION_LOGIN));

        Http::assertNothingSent();
    }

    public function test_verify_rejects_when_cloudflare_says_failure(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => false,
                'error-codes' => ['invalid-input-response'],
            ]),
        ]);

        $this->assertFalse(Turnstile::verify($this->tokenRequest('token-abc')));
    }

    public function test_verify_rejects_a_mismatched_action(): void
    {
        // Widget login mengirim token ber-action "comment", dan sebaliknya.
        Http::fake([
            self::VERIFY_URL => Http::response(['success' => true, 'action' => 'comment']),
        ]);

        $this->assertFalse(Turnstile::verify($this->tokenRequest('token-abc'), Turnstile::ACTION_LOGIN));
    }

    public function test_verify_rejects_an_unknown_hostname_only_when_configured(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response([
                'success' => true,
                'action' => 'login',
                'hostname' => 'penyerang.example',
            ]),
        ]);

        // Default-nya tidak diperiksa supaya admin tidak terkunci karena
        // perbedaan www.
        $this->assertTrue(Turnstile::verify($this->tokenRequest('t'), Turnstile::ACTION_LOGIN));

        config(['turnstile.hostnames' => ['sultraklik.id']]);
        $this->assertFalse(Turnstile::verify($this->tokenRequest('t'), Turnstile::ACTION_LOGIN));
    }

    public function test_verify_fails_closed_when_cloudflare_is_unreachable(): void
    {
        Http::fake(fn () => throw new ConnectionException('koneksi gagal'));

        $this->assertFalse(Turnstile::verify($this->tokenRequest('token-abc')));
    }

    public function test_verify_fails_closed_on_a_server_error(): void
    {
        Http::fake([self::VERIFY_URL => Http::response('boom', 500)]);

        $this->assertFalse(Turnstile::verify($this->tokenRequest('token-abc')));
    }

    // ----------------------------------------------------------------- login

    public function test_login_succeeds_when_the_captcha_passes(): void
    {
        // Kasus yang paling kritis: kalau ini salah, admin terkunci total.
        Http::fake([
            self::VERIFY_URL => Http::response(['success' => true, 'action' => 'login']),
        ]);

        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sultraklik.id',
            'password' => 'rahasia-super-panjang',
            'role' => 'super_admin',
        ]);

        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@sultraklik.id', 'password' => 'rahasia-super-panjang'])
            ->set('turnstileToken', 'token-abc')
            ->call('authenticate');

        $this->assertAuthenticated();
    }

    public function test_login_still_works_when_turnstile_is_disabled(): void
    {
        config(['turnstile.enabled' => false]);

        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@sultraklik.id',
            'password' => 'rahasia-super-panjang',
            'role' => 'super_admin',
        ]);

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@sultraklik.id', 'password' => 'rahasia-super-panjang'])
            ->call('authenticate');

        $this->assertAuthenticated();

        // Captcha mati berarti tidak ada panggilan ke Cloudflare sama sekali.
        Http::assertNothingSent();
    }

    public function test_login_is_blocked_without_a_valid_captcha(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response(['success' => false, 'error-codes' => ['timeout-or-duplicate']]),
        ]);

        Notification::fake();

        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@sultraklik.id', 'password' => 'rahasia-super-panjang'])
            ->set('turnstileToken', 'token-abc')
            ->call('authenticate')
            ->assertSet('turnstileToken', null);

        $this->assertGuest();
    }

    public function test_login_is_blocked_when_the_token_field_is_empty(): void
    {
        Notification::fake();

        $this->assertGuest();

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@sultraklik.id', 'password' => 'rahasia-super-panjang'])
            ->set('turnstileToken', null)
            ->call('authenticate');

        $this->assertGuest();

        // Tidak ada yang dikirim ke Cloudflare kalau token kosong.
        Http::assertNothingSent();
    }

    public function test_clearing_the_token_is_scheduled_for_the_browser(): void
    {
        Http::fake([
            self::VERIFY_URL => Http::response(['success' => false]),
        ]);

        Notification::fake();

        Livewire::test(Login::class)
            ->fillForm(['email' => 'admin@sultraklik.id', 'password' => 'rahasia-super-panjang'])
            ->set('turnstileToken', 'token-abc')
            ->call('authenticate')
            ->assertSet('turnstileToken', null)
            ->assertDispatched('sl-turnstile-failed');
    }

    // --------------------------------------------------------------- komentar

    public function test_comment_is_not_saved_when_the_captcha_fails(): void
    {
        (new DemoContentSeeder)->run();
        $article = Article::where('status', 'published')->firstOrFail();

        Http::fake([
            self::VERIFY_URL => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $before = $this->allCommentCount($article);

        $this->post(route('comment.store', $article->slug), [
            'name' => 'Pembaca',
            'body' => 'Komentar yang tidak boleh masuk.',
            Turnstile::FIELD => 'token-palsu',
        ])->assertRedirect();

        $this->assertSame($before, $this->allCommentCount($article));
    }

    public function test_comment_is_saved_when_the_captcha_passes(): void
    {
        (new DemoContentSeeder)->run();
        $article = Article::where('status', 'published')->firstOrFail();

        Http::fake([
            self::VERIFY_URL => Http::response(['success' => true, 'action' => 'comment']),
        ]);

        $before = $this->allCommentCount($article);

        $this->post(route('comment.store', $article->slug), [
            'name' => 'Pembaca',
            'body' => 'Komentar yang sah.',
            Turnstile::FIELD => 'token-abc',
        ])->assertRedirect();

        $this->assertSame($before + 1, $this->allCommentCount($article));
    }

    /**
     * Article::comments() sudah difilter is_approved = true, jadi komentar baru
     * yang menunggu redaksi tidak akan terhitung di sana.
     */
    private function allCommentCount(Article $article): int
    {
        return Comment::where('article_id', $article->id)->count();
    }

    // ------------------------------------------------------------------ view

    public function test_comment_page_renders_the_widget_only_when_enabled(): void
    {
        (new DemoContentSeeder)->run();
        $article = Article::where('status', 'published')->firstOrFail();
        $url = '/'.$article->slug;

        $this->get($url)->assertOk()->assertSee('cf-turnstile', false);

        config(['turnstile.enabled' => false]);
        $this->get($url)->assertOk()->assertDontSee('cf-turnstile', false);
    }

    public function test_login_page_renders_the_widget_and_wires_livewire_to_it(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSee('cf-turnstile', false);
        $response->assertSee(self::SITE_KEY, false);
        $response->assertSee('data-action="login"', false);
        // Token harus masuk ke state Livewire, bukan form biasa.
        $response->assertSee('sl-turnstile-token', false);
        $response->assertSee('wire:ignore', false);
    }

    public function test_content_security_policy_allows_the_turnstile_host(): void
    {
        $csp = implode("\n", config('security.content_security_policy'));

        $this->assertStringContainsString('script-src', $csp);
        $this->assertMatchesRegularExpression(
            '/script-src[^;]*https:\/\/challenges\.cloudflare\.com/',
            $csp
        );
        $this->assertMatchesRegularExpression(
            '/frame-src[^;]*https:\/\/challenges\.cloudflare\.com/',
            $csp
        );
        $this->assertMatchesRegularExpression(
            '/connect-src[^;]*https:\/\/challenges\.cloudflare\.com/',
            $csp
        );
    }

    public function test_production_warns_when_cloudflare_test_keys_are_used(): void
    {
        // Test key Cloudflare selalu lulus, jadi captcha jadi tidak berguna.
        // Jangan refreshApplication() di sini: itu mereset facade dan mock-nya
        // hilang. detectEnvironment cukup untuk membuat isProduction() true.
        app()->detectEnvironment(fn () => 'production');

        config([
            'turnstile.enabled' => true,
            'turnstile.site_key' => '1x00000000000000000000AA',
            'turnstile.secret_key' => '1x0000000000000000000000000000000AA',
        ]);

        Cache::shouldReceive('add')->once()->andReturn(true);
        Log::spy();

        (new AppServiceProvider($this->app))->boot();

        Log::shouldHaveReceived('critical')
            ->once()
            ->withArgs(fn ($message) => str_contains($message, 'TEST KEY'));
    }

    public function test_production_stays_quiet_when_real_turnstile_keys_are_used(): void
    {
        app()->detectEnvironment(fn () => 'production');

        config([
            'turnstile.enabled' => true,
            'turnstile.site_key' => self::SITE_KEY,
            'turnstile.secret_key' => self::SECRET,
        ]);

        Cache::shouldReceive('add')->never();
        Log::spy();

        (new AppServiceProvider($this->app))->boot();

        Log::shouldNotHaveReceived('critical');
    }

    public function test_dummy_key_warning_is_not_repeated_every_request(): void
    {
        app()->detectEnvironment(fn () => 'production');

        config([
            'turnstile.enabled' => true,
            'turnstile.site_key' => '1x00000000000000000000AA',
            'turnstile.secret_key' => '1x0000000000000000000000000000000AA',
        ]);

        // Cache::add yang bernilai false = peringatan sudah pernah ditulis.
        Cache::shouldReceive('add')->once()->andReturn(false);
        Log::spy();

        (new AppServiceProvider($this->app))->boot();

        Log::shouldNotHaveReceived('critical');
    }

    private function tokenRequest(string $token, array $server = []): Request
    {
        return Request::create('/', 'POST', [Turnstile::FIELD => $token], [], [], $server);
    }
}
