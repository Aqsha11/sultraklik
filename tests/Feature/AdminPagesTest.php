<?php

namespace Tests\Feature;

use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pages_render_for_authenticated_user(): void
    {
        $admin = User::where('email', 'admin@sultraklik.com')->first()
            ?? User::create([
                'name' => 'Admin',
                'email' => 'admin@sultraklik.com',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
            ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/articles')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/articles/create')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/categories')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/regions')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/tags')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/media')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/breaking-news')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/advertisements')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/pages')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/settings-page')
            ->assertOk()
            ->assertSee('Warna Utama')
            ->assertSee('Warna Aksen');

        $this->actingAs($admin)
            ->get('/admin/manage-headlines')
            ->assertOk();
    }

    public function test_login_page_renders_with_custom_layout(): void
    {
        config(['app.debug' => true]);

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('Masuk ke Dashboard')
            ->assertSee('Alamat Email')
            ->assertSee('Kata Sandi')
            ->assertSee('Ingat saya')
            ->assertSee('Tetap masuk di perangkat ini selama 30 hari.', false)
            ->assertSee('Masuk', false)
            ->assertSee('Kembali ke beranda')
            ->assertSee('getModifierState', false);
    }

    public function test_article_form_only_accepts_youtube_link(): void
    {
        $admin = User::create([
            'name' => 'Admin Video',
            'email' => 'admin-video@sultraklik.com',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $category = Category::create([
            'name' => 'Umum',
            'slug' => 'umum-video',
            'is_active' => true,
        ]);

        $article = Article::create([
            'title' => 'Berita Dengan Video',
            'content' => '<p>Isi berita.</p>',
            'category_id' => $category->id,
            'author_id' => $admin->id,
            'status' => Article::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        $this->actingAs($admin);

        Livewire::test(EditArticle::class, ['record' => $article->getKey()])
            ->fillForm(['video_url' => 'https://vimeo.com/123456789'])
            ->call('save')
            ->assertHasFormErrors(['video_url']);

        $this->assertNull($article->fresh()->video_url);

        Livewire::test(EditArticle::class, ['record' => $article->getKey()])
            ->fillForm(['video_url' => 'https://www.youtube.com/shorts/dQw4w9WgXcQ'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('https://www.youtube.com/watch?v=dQw4w9WgXcQ', $article->fresh()->video_url);

        $this->get('/admin/articles/'.$article->id.'/edit')
            ->assertOk()
            ->assertSee('i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false);
    }

    public function test_advertisement_thumbnail_url_is_same_origin_and_not_double_prefixed(): void
    {
        $admin = User::where('email', 'admin@sultraklik.com')->first()
            ?? User::create([
                'name' => 'Admin',
                'email' => 'admin@sultraklik.com',
                'password' => 'password',
                'role' => User::ROLE_SUPER_ADMIN,
            ]);

        $path = 'ads/thumbnail-uji.png';
        Storage::disk('public')->put($path, $this->onePixelPng());

        Advertisement::create([
            'title' => 'Iklan Uji Thumbnail',
            'position' => 'sidebar',
            'type' => 'image',
            'image' => $path,
            'is_active' => true,
        ]);

        $html = $this->actingAs($admin)
            ->get('/admin/advertisements')
            ->assertOk()
            ->getContent();

        // ImageColumn hanya memperlakukan state sebagai URL bila filter_var
        // FILTER_VALIDATE_URL lolos. Path relatif /storage/... tidak lolos, lalu
        // digabung lagi dengan disk -> /storage/storage/... dan thumbnail hilang.
        $this->assertStringContainsString('src="/storage/'.$path.'"', $html);
        $this->assertStringNotContainsString('/storage/storage/', $html);
    }

    private function onePixelPng(): string
    {
        return (string) base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
            true
        );
    }
}
