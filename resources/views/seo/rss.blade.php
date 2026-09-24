{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>SULTRAKLIK — Portal Berita Sulawesi Tenggara</title>
        <link>{{ url('/') }}</link>
        <description>{{ \App\Models\Setting::get('general.description') }}</description>
        <language>id-id</language>
        <lastBuildDate>{{ now()->toRssString() }}</lastBuildDate>
        <atom:link href="{{ url('/rss.xml') }}" rel="self" type="application/rss+xml"/>
        @foreach($articles as $article)
            <item>
                <title>{{ $article->title }}</title>
                <link>{{ url($article->slug) }}</link>
                <guid isPermaLink="true">{{ url($article->slug) }}</guid>
                <pubDate>{{ $article->published_at->toRssString() }}</pubDate>
                <dc:creator xmlns:dc="http://purl.org/dc/elements/1.1/">{{ $article->author?->name }}</dc:creator>
                <category>{{ $article->category?->name }}</category>
                <description><![CDATA[{!! $article->excerpt !!}]]></description>
                @if($article->featured_image_url)
                    <enclosure url="{{ $article->featured_image_url }}" type="image/jpeg"/>
                @endif
            </item>
        @endforeach
    </channel>
</rss>