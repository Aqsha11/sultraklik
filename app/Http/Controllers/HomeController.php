<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Headline;
use App\Models\Region;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $headlineMain = Headline::active()->where('position', 1)->with('article')->first();
        $headlineOthers = Headline::active()
            ->where('position', '>', 1)
            ->orderBy('position')
            ->limit(4)
            ->with('article')
            ->get()
            ->pluck('article')
            ->filter();

        $excludedIds = collect([$headlineMain?->article_id])
            ->merge($headlineOthers->pluck('id'))
            ->filter()
            ->unique();

        $latest = Article::published()
            ->with(['category', 'region', 'author'])
            ->whereNotIn('id', $excludedIds)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();

        $popular = Article::published()
            ->with(['category'])
            ->orderByDesc('views')
            ->limit(5)
            ->get();

        $regions = Region::where('is_active', true)
            ->orderBy('order_column')
            ->withCount(['articles' => fn ($q) => $q->published()])
            ->get();

        $breakingNews = BreakingNews::live()->first();

        $sections = [];
        foreach (['peristiwa', 'politik', 'pemerintahan', 'ekonomi', 'pendidikan', 'kesehatan'] as $slug) {
            $category = \App\Models\Category::where('slug', $slug)->first();
            if ($category) {
                $sections[$slug] = Article::published()
                    ->where('category_id', $category->id)
                    ->whereNotIn('id', $excludedIds)
                    ->with(['category', 'region'])
                    ->orderByDesc('published_at')
                    ->limit(4)
                    ->get();
            }
        }

        return view('home', compact(
            'headlineMain',
            'headlineOthers',
            'latest',
            'popular',
            'regions',
            'breakingNews',
            'sections'
        ));
    }
}