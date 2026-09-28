<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Page;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Fixtures\DemoContentSeeder;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DemoContentSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $article = Article::published()->firstOrFail();
        $category = Category::where('is_active', true)->firstWhere('slug', 'politik') ?? Category::where('is_active', true)->first();
        $region = Region::where('is_active', true)->firstOrFail();
        $tag = $article->tags->first();

        $this->get('/')->assertOk()->assertSee('SULTRAKLIK');
        $this->get('/sultra')->assertOk();
        $this->get('/kategori/'.$category->slug)->assertOk();
        $this->get('/sultra/'.$region->slug)->assertOk();
        $this->get('/page/tentang-kami')->assertOk();
        $this->get('/search?q=konawe')->assertOk();
        $this->get('/rss.xml')->assertOk()->assertSee('<rss', false);
        $this->get('/sitemap.xml')->assertOk()->assertSee('<urlset', false);
        $this->get('/'.$article->slug)->assertOk()->assertSee($article->title);

        if ($tag !== null) {
            $this->get('/tag/'.$tag->slug)->assertOk();
        }
    }

    public function test_visitor_can_submit_comment(): void
    {
        $article = Article::published()->firstOrFail();

        $this->from('/'.$article->slug)
            ->post('/komentar/'.$article->slug, [
                'name' => 'Pengunjung',
                'email' => 'pengunjung@example.com',
                'body' => 'Berita yang bagus sekali.',
            ])
            ->assertRedirect('/'.$article->slug)
            ->assertSessionHas('comment_status');

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'name' => 'Pengunjung',
            'body' => 'Berita yang bagus sekali.',
            'is_approved' => false,
        ]);

        $this->assertTrue(Comment::where('is_approved', false)->count() >= 1);

        $this->get('/'.$article->slug)
            ->assertOk()
            ->assertSee('Kirim Komentar');
    }

    public function test_night_mode_toggle_is_available_on_public_pages(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('css/night.css', false)
            ->assertSee("localStorage.getItem('sultraklik-theme')", false)
            ->assertSee('function themeToggle()', false)
            ->assertSee('Mode Gelap')
            ->assertSee("setAttribute('data-theme', 'dark')", false);

        $this->assertFileExists(public_path('css/night.css'));
        $this->assertStringContainsString(
            '[data-theme="dark"] body',
            (string) file_get_contents(public_path('css/night.css'))
        );
        $this->assertStringContainsString(
            '[data-theme="dark"] .bg-white',
            (string) file_get_contents(public_path('css/night.css'))
        );
    }

    public function test_homepage_shows_video_section(): void
    {
        $video = Article::published()->whereNotNull('video_url')->firstOrFail();

        $this->get('/')
            ->assertOk()
            ->assertSee('Putar video bersuara: '.$video->title, false)
            ->assertSee('@mouseenter="if (! pinned) playing = true"', false)
            ->assertSee('i.ytimg.com/vi/'.$video->youtube_id.'/hqdefault.jpg', false)
            ->assertSee('youtube-nocookie.com/embed/'.$video->youtube_id, false);
    }

    public function test_youtube_video_is_embedded_without_redirecting(): void
    {
        $article = Article::published()->firstOrFail();
        $article->update(['video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s']);
        $article->refresh();

        $this->get('/'.$article->slug)
            ->assertOk()
            ->assertSee('i.ytimg.com/vi/dQw4w9WgXcQ/hqdefault.jpg', false)
            ->assertSee('www.youtube-nocookie.com/embed/dQw4w9WgXcQ', false)
            ->assertSee('Putar video YouTube', false);
    }

    public function test_article_without_video_has_no_player(): void
    {
        $article = Article::published()->whereNull('video_url')->firstOrFail();

        $this->get('/'.$article->slug)
            ->assertOk()
            ->assertDontSee('youtube-nocookie.com/embed', false);
    }

    public function test_visitor_can_like_article(): void
    {
        $article = Article::published()->firstOrFail();
        $before = $article->likes_count;

        $this->post('/artikel/'.$article->slug.'/like', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson(['liked' => true, 'likes' => $before + 1]);

        $this->assertDatabaseHas('article_likes', [
            'article_id' => $article->id,
        ]);

        $this->post('/artikel/'.$article->slug.'/like', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()
            ->assertJson(['liked' => false, 'likes' => $before]);
    }

    public function test_seeded_images_are_files_on_the_public_disk(): void
    {
        $disk = Storage::disk('public');

        $images = Article::whereNotNull('featured_image')
            ->pluck('featured_image')
            ->merge(Advertisement::whereNotNull('image')->pluck('image'))
            ->filter();

        $this->assertGreaterThan(0, $images->count());

        foreach ($images as $image) {
            // Filament FileUpload me-resolve preview lewat Storage::exists() lalu
            // Storage::url(), jadi URL eksternal membuat preview tidak pernah tampil.
            $this->assertFalse(Str::startsWith($image, ['http://', 'https://']), "URL eksternal: {$image}");
            $this->assertTrue($disk->exists($image), "File hilang: {$image}");
        }
    }

    public function test_storage_urls_stay_on_the_current_origin(): void
    {
        $this->assertStringStartsWith('/storage/', Storage::disk('public')->url('featured/contoh.png'));

        // APP_URL sering tidak sama dengan origin yang dipakai browser (port
        // dev, Valet, IP lokal). URL absolut hasil asset()/Storage::url() lalu
        // jadi cross-origin: CSP connect-src 'self' memblokir fetch preview
        // Filament, dan gambar publik gagal dimuat. Halaman tidak boleh
        // memuat URL storage absolut.
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame([], $this->absoluteStorageUrls($html));
    }

    public function test_navbar_marks_the_current_page_not_always_beranda(): void
    {
        $category = Category::where('slug', '!=', 'sultra')->where('is_active', true)->firstOrFail();

        // Beranda: hanya BERANDA yang aktif.
        $this->assertSame(['BERANDA'], $this->activeNavLabels($this->get('/')->assertOk()));

        // Halaman kategori: BERANDA harus mati, kategori yang menyala.
        $labels = $this->activeNavLabels($this->get('/kategori/'.$category->slug)->assertOk());
        $this->assertContains(strtoupper($category->name), $labels);
        $this->assertNotContains('BERANDA', $labels);

        // Halaman statis dari navbar.
        $page = Page::where('is_active', true)->firstOrFail();
        $this->assertContains(strtoupper($page->title), $this->activeNavLabels($this->get('/page/'.$page->slug)->assertOk()));

        // Membaca artikel menyorot kategori induknya.
        $article = Article::published()->where('category_id', $category->id)->firstOrFail();
        $this->assertContains(
            strtoupper($category->name),
            $this->activeNavLabels($this->get('/'.$article->slug)->assertOk()),
        );

        // SULTRA adalah <button> dropdown, jadi dicek dari kelas tombolnya.
        $this->assertTrue($this->sultraButtonIsActive($this->get('/sultra')->assertOk()));
        $this->assertFalse($this->sultraButtonIsActive($this->get('/')->assertOk()));
        $this->assertFalse($this->sultraButtonIsActive($this->get('/kategori/'.$category->slug)->assertOk()));
    }

    /**
     * Label link navbar yang sedang aktif, dibaca dari kelas aktif yang
     * di-render server (`text-red-600 border-b-2 border-red-600`).
     *
     * @return list<string>
     */
    private function activeNavLabels(TestResponse $response): array
    {
        preg_match_all(
            '/class="[^"]*border-red-600[^"]*"[^>]*>\s*([^<]+)</',
            $response->getContent(),
            $matches,
        );

        // Label bisa berisi spasi ("TENTANG KAMI"), normalkan biar bisa
        // dibandingkan dengan strtoupper($page->title).
        return array_values(array_unique(array_map(
            fn (string $label) => strtoupper(trim($label)),
            $matches[1],
        )));
    }

    private function sultraButtonIsActive(TestResponse $response): bool
    {
        preg_match('/<button[^>]*sultraOpen = !sultraOpen"[^>]*>/', $response->getContent(), $matches);

        return str_contains($matches[0] ?? '', 'border-red-600');
    }

    /** @return list<string> */
    private function absoluteStorageUrls(string $html): array
    {
        preg_match_all('#[\'"]https?://[^\'"\s]*/storage/#', $html, $matches);

        return $matches[0];
    }
}
