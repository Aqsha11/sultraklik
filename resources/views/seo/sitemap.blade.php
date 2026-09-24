{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>{{ url('/') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>{{ url('/sultra') }}</loc>
        <changefreq>hourly</changefreq>
        <priority>0.9</priority>
    </url>
    @foreach(\App\Models\Category::where('is_active', true)->get() as $category)
        <url>
            <loc>{{ url('/kategori/'.$category->slug) }}</loc>
            <changefreq>daily</changefreq>
            <priority>0.8</priority>
        </url>
    @endforeach
    @foreach($articles as $article)
        <url>
            <loc>{{ url($article->slug) }}</loc>
            <lastmod>{{ $article->updated_at->toIso8601String() }}</lastmod>
            <changefreq>daily</changefreq>
            <priority>0.7</priority>
            <news:news>
                <news:publication>
                    <news:name>SULTRAKLIK</news:name>
                    <news:language>id</news:language>
                </news:publication>
                <news:publication_date>{{ $article->published_at->toIso8601String() }}</news:publication_date>
                <news:title>{{ $article->title }}</news:title>
            </news:news>
        </url>
    @endforeach
    @foreach($pages as $page)
        <url>
            <loc>{{ url('/page/'.$page->slug) }}</loc>
            <lastmod>{{ $page->updated_at->toIso8601String() }}</lastmod>
            <changefreq>monthly</changefreq>
            <priority>0.3</priority>
        </url>
    @endforeach
</urlset>