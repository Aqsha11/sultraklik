<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(Article $article): View|RedirectResponse
    {
        if (! $article->isPublished()) {
            abort(404);
        }

        $article->increment('views');

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
            'comments' => $article->comments()->latest()->limit(50)->get(),
        ]);
    }
}
