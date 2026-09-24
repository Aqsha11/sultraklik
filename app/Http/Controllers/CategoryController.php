<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Region;
use App\Models\Tag;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Category $category): View
    {
        $slug = strtolower($category->slug);

        // Jika kategori "sultra", tampilkan berita seluruh wilayah
        if ($slug === 'sultra') {
            return $this->sultra();
        }

        $articles = \App\Models\Article::published()
            ->where('category_id', $category->id)
            ->with(['category', 'region'])
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('categories.show', [
            'title' => $category->name,
            'articles' => $articles,
            'regions' => Region::where('is_active', true)->orderBy('order_column')->get(),
        ]);
    }

    public function sultra(): View
    {
        $articles = \App\Models\Article::published()
            ->whereNotNull('region_id')
            ->with(['category', 'region'])
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('categories.show', [
            'title' => 'Sulawesi Tenggara',
            'articles' => $articles,
            'regions' => Region::where('is_active', true)->orderBy('order_column')->get(),
        ]);
    }

    public function byRegion(Region $region): View
    {
        $articles = \App\Models\Article::published()
            ->where('region_id', $region->id)
            ->with(['category', 'region'])
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('categories.show', [
            'title' => $region->name,
            'articles' => $articles,
            'regions' => Region::where('is_active', true)->orderBy('order_column')->get(),
        ]);
    }

    public function byTag(Tag $tag): View
    {
        $articles = $tag->articles()
            ->published()
            ->with(['category', 'region'])
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('categories.show', [
            'title' => '#'.$tag->name,
            'articles' => $articles,
            'regions' => Region::where('is_active', true)->orderBy('order_column')->get(),
        ]);
    }
}