<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Headline;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $headlineMain = Headline::active()->where('position', 1)->with('article')->first();
        $headlineOthers = Headline::active()
            ->where('position', '>', 1)
            ->orderBy('position')
            ->limit(5)
            ->with('article')
            ->get()
            ->pluck('article')
            ->filter();

        $usedIds = collect([$headlineMain?->article_id])
            ->merge($headlineOthers->pluck('id'))
            ->filter()
            ->unique()
            ->all();

        $latest = Article::published()
            ->with(['category', 'region', 'author'])
            ->whereNotIn('id', $usedIds)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();
        $usedIds = array_merge($usedIds, $latest->pluck('id')->all());

        $popular = Article::published()
            ->with(['category', 'region'])
            ->whereNotIn('id', $usedIds)
            ->orderByDesc('views')
            ->limit(3)
            ->get();
        $usedIds = array_merge($usedIds, $popular->pluck('id')->all());

        $pickedBase = Article::published()
            ->with(['category', 'region'])
            ->where('is_featured', true)
            ->where('is_headline', false)
            ->whereNotIn('id', $usedIds)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();
        $picked = $this->fillSection($pickedBase, 4, $usedIds);

        $recommendedBase = Article::published()
            ->with(['category', 'region'])
            ->where('is_headline', false)
            ->whereNotIn('id', $usedIds)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();
        $recommended = $this->fillSection($recommendedBase, 10, $usedIds);

        $wilayahBase = Article::published()
            ->with(['category', 'region'])
            ->whereNotNull('region_id')
            ->where('is_headline', false)
            ->whereNotIn('id', $usedIds)
            ->orderByDesc('published_at')
            ->limit(10)
            ->get();
        $wilayahArticles = $this->fillSection($wilayahBase, 10, $usedIds);

        $breakingNews = BreakingNews::live()->first();

        return view('home', compact(
            'headlineMain',
            'headlineOthers',
            'latest',
            'popular',
            'picked',
            'recommended',
            'wilayahArticles',
            'breakingNews'
        ) + ['pageUsedIds' => $usedIds]);
    }

    public function loadMore(Request $request): JsonResponse
    {
        $section = $request->query('section', 'latest');
        $exclude = collect(explode(',', (string) $request->query('exclude', '')))
            ->filter(fn ($value) => is_numeric($value))
            ->map(fn ($value) => (int) $value)
            ->values();

        $query = Article::published()
            ->with(['category', 'region', 'author'])
            ->whereNotIn('id', $exclude)
            ->orderByDesc('published_at')
            ->limit(6);

        if ($section !== 'latest') {
            $query->where('is_headline', false);
        }

        $articles = $query->get();

        $html = '';
        foreach ($articles as $article) {
            $html .= view('components.news-card', [
                'article' => $article,
                'textSize' => 'text-lg',
                'showImage' => true,
            ])->render();
        }

        return response()->json([
            'html' => $html,
            'ids' => $articles->pluck('id')->all(),
            'hasMore' => $articles->count() === 6,
        ]);
    }

    private function fillSection(Collection $articles, int $target, array &$usedIds): Collection
    {
        $articles = $articles->reject(fn (Article $article) => in_array($article->id, $usedIds))
            ->values()
            ->take($target);

        $missing = $target - $articles->count();
        if ($missing > 0) {
            $fillers = Article::published()
                ->with(['category', 'region'])
                ->whereNotIn('id', array_merge($usedIds, $articles->pluck('id')->all()))
                ->orderByDesc('published_at')
                ->limit($missing)
                ->get();
            $articles = $articles->concat($fillers)->take($target)->values();
        }

        $usedIds = array_merge($usedIds, $articles->pluck('id')->all());

        return $articles;
    }
}
