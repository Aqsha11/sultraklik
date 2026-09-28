<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $role.'@sultraklik.com',
            'password' => 'password',
            'role' => $role,
        ]);
    }

    public function test_production_seed_refuses_to_create_super_admin_without_a_password(): void
    {
        // dulu seeder membuat tiga akun demo dengan password 'password'. Kalau
        // guard ini hilang, akun super admin produksi bisa dibuat tanpa rahasia.
        app()->detectEnvironment(fn () => 'production');
        putenv('SUPER_ADMIN_PASSWORD');
        $_ENV['SUPER_ADMIN_PASSWORD'] = null;
        $_SERVER['SUPER_ADMIN_PASSWORD'] = null;

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessageMatches('/SUPER_ADMIN_PASSWORD/');

        (new UserSeeder)->run();
    }

    public function test_production_seed_rejects_a_too_short_password(): void
    {
        app()->detectEnvironment(fn () => 'production');
        putenv('SUPER_ADMIN_PASSWORD=pendek123');
        $_ENV['SUPER_ADMIN_PASSWORD'] = 'pendek123';

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessageMatches('/minimal 12 karakter/');

            (new UserSeeder)->run();
        } finally {
            putenv('SUPER_ADMIN_PASSWORD');
            unset($_ENV['SUPER_ADMIN_PASSWORD']);
        }
    }

    public function test_production_seed_uses_the_given_password_and_only_creates_super_admin(): void
    {
        app()->detectEnvironment(fn () => 'production');
        $password = 'Kx9#mQ2vLp7$Zn4W';
        putenv("SUPER_ADMIN_PASSWORD={$password}");

        try {
            (new UserSeeder)->run();

            $this->assertSame(1, User::count());
            $this->assertSame(User::ROLE_SUPER_ADMIN, User::first()->role);
            $this->assertTrue(Hash::check($password, User::first()->password));

            // db:seed ulang tidak boleh menimpa password yang sudah diganti.
            (new UserSeeder)->run();
            $this->assertSame(1, User::count());
            $this->assertTrue(Hash::check($password, User::first()->password));
        } finally {
            putenv('SUPER_ADMIN_PASSWORD');
            unset($_ENV['SUPER_ADMIN_PASSWORD']);
        }
    }

    private function makeArticle(array $overrides = []): Article
    {
        $category = Category::first() ?? Category::create([
            'name' => 'Umum',
            'slug' => 'umum',
            'is_active' => true,
        ]);

        return Article::create(array_merge([
            'title' => 'Berita Uji Keamanan',
            'slug' => 'berita-uji-keamanan',
            'excerpt' => 'Ringkasan berita uji.',
            'content' => '<p>Isi berita.</p>',
            'category_id' => $category->id,
            'author_id' => $this->makeUser(User::ROLE_EDITOR)->id,
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ], $overrides));
    }

    public function test_reporter_cannot_open_user_management(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_REPORTER))
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_editor_cannot_open_user_management_but_admin_pages_remain_open(): void
    {
        $editor = $this->makeUser(User::ROLE_EDITOR);

        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/categories')->assertOk();
        $this->actingAs($editor)->get('/admin/manage-headlines')->assertOk();
    }

    public function test_settings_page_is_denied_for_editor(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_EDITOR))
            ->get('/admin/settings-page')
            ->assertForbidden();
    }

    public function test_settings_page_is_open_for_admin(): void
    {
        $this->actingAs($this->makeUser(User::ROLE_ADMIN))
            ->get('/admin/settings-page')
            ->assertOk();
    }

    public function test_login_page_renders_for_guests(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_super_admin_keeps_full_access(): void
    {
        $admin = $this->makeUser(User::ROLE_SUPER_ADMIN);

        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/settings-page')->assertOk();
        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_reporter_cannot_upload_images(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeUser(User::ROLE_REPORTER))
            ->post('/upload-image', ['upload' => UploadedFile::fake()->image('foto.jpg')])
            ->assertForbidden();
    }

    public function test_editor_upload_stores_file_on_public_disk(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->makeUser(User::ROLE_EDITOR))
            ->post('/upload-image', ['upload' => UploadedFile::fake()->image('foto.jpg')]);

        $response->assertOk()->assertJsonStructure(['url']);

        $url = $response->json('url');

        // WAJIB root-relative. Kalau absolut (dari APP_URL) dan APP_URL != origin
        // yang dipakai browser, CSP connect-src 'self' memblokir fetch preview
        // Filament dan <img> di situs publik gagal dimuat.
        $this->assertStringStartsWith('/storage/', $url);

        $path = Str::after($url, '/storage/');

        Storage::disk('public')->assertExists($path);
        $this->assertStringStartsWith('uploads/ckeditor/', $path);
    }

    public function test_upload_rejects_file_that_is_not_a_real_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->makeUser(User::ROLE_EDITOR))
            ->post('/upload-image', [
                'upload' => UploadedFile::fake()->create('payload.jpg', 10, 'image/jpeg'),
            ])
            ->assertStatus(422);
    }

    public function test_article_content_is_sanitized_on_save(): void
    {
        $article = $this->makeArticle([
            'content' => '<p onclick="alert(1)">Halo</p>'
                .'<script>alert(document.cookie)</script>'
                .'<img src="x" onerror="alert(1)">'
                .'<a href="javascript:alert(1)">jahat</a>'
                .'<a href="https://sultraklik.com" target="_blank">aman</a>',
        ]);

        $article->refresh();

        $this->assertStringNotContainsString('<script', $article->content);
        $this->assertStringNotContainsString('onclick', $article->content);
        $this->assertStringNotContainsString('onerror', $article->content);
        $this->assertStringNotContainsString('javascript:', $article->content);
        $this->assertStringContainsString('Halo', $article->content);
        $this->assertStringContainsString('https://sultraklik.com', $article->content);
        $this->assertStringContainsString('noopener', $article->content);
    }

    public function test_article_page_never_renders_script_tags(): void
    {
        $article = $this->makeArticle([
            'slug' => 'artikel-berita-aman',
            'content' => '<p>Selamat datang</p>',
        ]);

        $this->get('/'.$article->slug)
            ->assertOk()
            ->assertSee('Selamat datang', false)
            ->assertDontSee('<script>alert', false);
    }

    public function test_reserved_slugs_are_rewritten(): void
    {
        $this->assertSame('search-berita', Article::normalizeSlug('search'));
        $this->assertSame('sultra-berita', Article::normalizeSlug('Sultra'));
        $this->assertSame('kategori-berita', Article::normalizeSlug('kategori'));
        $this->assertSame('berita-normal', Article::normalizeSlug('Berita Normal'));
        $this->assertStringStartsWith('artikel-', Article::normalizeSlug('!!!'));
    }

    public function test_saving_article_with_reserved_slug_is_rewritten(): void
    {
        $article = $this->makeArticle(['slug' => 'search', 'title' => 'Berita_SLUR']);

        $this->assertSame('search-berita', $article->fresh()->slug);
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

        $this->assertStringContainsString("object-src 'none'", (string) $this->get('/')->headers->get('Content-Security-Policy'));
    }

    public function test_comment_submission_is_rate_limited(): void
    {
        $article = $this->makeArticle(['slug' => 'berita-limit-komentar']);

        for ($i = 0; $i < 5; $i++) {
            $this->from('/'.$article->slug)
                ->post('/komentar/'.$article->slug, [
                    'name' => 'Pengunjung',
                    'email' => 'pengunjung@example.com',
                    'body' => 'Komentar '.$i,
                ])
                ->assertRedirect('/'.$article->slug);
        }

        $this->assertDatabaseCount('comments', 5);

        $this->from('/'.$article->slug)
            ->post('/komentar/'.$article->slug, [
                'name' => 'Spammer',
                'email' => 'spam@example.com',
                'body' => 'Iklanetted',
            ])
            ->assertRedirect('/'.$article->slug)
            ->assertSessionHas('comment_error');

        $this->assertDatabaseCount('comments', 5);
    }

    public function test_view_counter_ignores_repeated_hits_from_same_visitor(): void
    {
        $article = $this->makeArticle(['slug' => 'berita-hitung-views']);

        $this->get('/berita-hitung-views')->assertOk();
        $this->get('/berita-hitung-views')->assertOk();

        $this->assertSame(1, $article->fresh()->views);
    }

    public function test_only_youtube_urls_are_accepted_as_video(): void
    {
        $hostile = [
            'javascript:alert(1)',
            'https://evil.example.com/watch?v=dQw4w9WgXcQ',
            '//youtube.com.evil.example.com/watch?v=dQw4w9WgXcQ',
            'https://www.youtube.com/watch?v=pendek',
            'https://www.youtube.com/../../evil',
        ];

        $article = $this->makeArticle(['slug' => 'berita-video-tidak-valid']);

        foreach ($hostile as $url) {
            $article->update(['video_url' => $url]);

            $this->assertNull($article->fresh()->video_url, 'URL non-YouTube tidak boleh disimpan: '.$url);
            $this->assertFalse($article->hasVideo());

            $this->get('/'.$article->slug)
                ->assertOk()
                ->assertDontSee('evil.example.com', false)
                ->assertDontSee('youtube-nocookie.com/embed', false);
        }
    }

    public function test_youtube_url_is_normalized_to_canonical_watch_link(): void
    {
        $article = $this->makeArticle(['video_url' => 'https://youtu.be/dQw4w9WgXcQ?si=abc123']);

        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $article->fresh()->video_url);
        $this->assertSame('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', $article->video_embed_url);
    }
}
