<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class SiteMapController extends Controller
{
    public function index(): Response
    {
        $articles = Article::published()->orderByDesc('published_at')->get(['slug', 'updated_at', 'published_at', 'title']);
        $pages = \App\Models\Page::where('is_active', true)->get(['slug', 'updated_at']);

        $content = view('seo.sitemap', compact('articles', 'pages'))->render();

        return response($content, 200)->header('Content-Type', 'application/xml');
    }

    public function rss(): Response
    {
        $articles = Article::published()
            ->with(['category', 'author'])
            ->orderByDesc('published_at')
            ->limit(30)
            ->get();

        $content = view('seo.rss', compact('articles'))->render();

        return response($content)->header('Content-Type', 'application/rss+xml');
    }
}