<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Region;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_public_pages_render(): void
    {
        $article = Article::published()->firstOrFail();
        $category = Category::where('is_active', true)->firstWhere('slug', 'politik') ?? Category::where('is_active', true)->first();
        $region = Region::where('is_active', true)->where('is_region', true)->firstOr(fn () => Region::firstOrFail());
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
}
