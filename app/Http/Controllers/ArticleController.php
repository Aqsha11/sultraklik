<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class ArticleController extends Controller
{
    private const VIEW_COUNT_TTL = 1800;

    public function show(Request $request, Article $article): View|RedirectResponse
    {
        if (! $article->isPublished()) {
            abort(404);
        }

        $this->countView($request, $article);

        $related = Article::published()
            ->where('id', '!=', $article->id)
            ->where(fn ($q) => $q
                ->where('category_id', $article->category_id)
                ->orWhere('region_id', $article->region_id))
            ->with(['category', 'region'])
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('articles.show', [
            'article' => $article->load(['category', 'region', 'author', 'tags']),
            'related' => $related,
            'comments' => $article->comments()
                ->where('is_approved', true)
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }

    private function countView(Request $request, Article $article): void
    {
        $key = 'article-view:'.sha1($article->id.'|'.$request->ip().'|'.$request->userAgent());

        if (Cache::add($key, true, self::VIEW_COUNT_TTL)) {
            $article->increment('views');
        }
    }
}
