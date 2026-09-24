<?php

namespace App\Http\Controllers;

use App\Models\Article;
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
            ->paginate(12)
            ->withQueryString();

        return view('search', [
            'query' => $query,
            'articles' => $articles,
        ]);
    }
}