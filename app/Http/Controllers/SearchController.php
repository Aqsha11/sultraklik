<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim($request->query('q', ''));

        $articles = Article::published()
            ->with(['category', 'region'])
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($builder) use ($query) {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('content', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('published_at')
            ->paginate(26)
            ->withQueryString();

        return view('search', [
            'query' => $query,
            'articles' => $articles,
        ]);
    }

    public function live(Request $request): JsonResponse
    {
        $query = trim($request->query('q', ''));

        $articles = Article::published()
            ->with(['category', 'region'])
            ->when($query !== '', function ($q) use ($query) {
                $q->where(function ($builder) use ($query) {
                    $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%");
                });
            })
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return response()->json($articles->map(fn (Article $article) => [
            'slug' => $article->slug,
            'title' => $article->title,
            'published_at' => $article->published_at?->translatedFormat('d M Y'),
            'category' => $article->category?->name,
            'region' => $article->region?->name,
            'image' => $article->featured_image_url,
        ]));
    }
}
