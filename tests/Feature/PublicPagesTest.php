<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Region;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
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
}