@props([
    'title' => 'SULTRAKLIK',
    'metaDescription' => null,
    'metaImage' => null,
    'article' => null,
])
@php
    $brandColor = \App\Models\Setting::get('theme.primary_color', '#dc2626');
    $accentColor = \App\Models\Setting::get('theme.accent_color', $brandColor);
    $brandPalette = \App\Support\ColorPalette::shades($brandColor);
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? ($title . ' — SULTRAKLIK') : 'SULTRAKLIK' }}</title>
    <meta name="description" content="{{ $metaDescription ?? \App\Models\Setting::get('general.tagline', 'Portal Berita Sulawesi Tenggara') }}">

    <meta property="og:site_name" content="SULTRAKLIK">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    @if($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif
    <meta property="og:type" content="{{ $article ? 'article' : 'website' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta property="og:url" content="{{ url()->current() }}">

    @if($article)
        <meta property="article:published_time" content="{{ $article->published_at?->toIso8601String() }}">
        <meta property="article:modified_time" content="{{ $article->updated_at->toIso8601String() }}">
        <meta name="author" content="{{ $article->author?->name }}">
        @foreach($article->tags as $tag)
            <meta property="article:tag" content="{{ $tag->name }}">
        @endforeach
        <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $article->title,
            'image' => [$article->seo_image],
            'datePublished' => $article->published_at?->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $article->author?->name],
            'publisher' => ['@type' => 'Organization', 'name' => 'SULTRAKLIK'],
            'description' => $article->excerpt,
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" type="application/rss+xml" title="SULTRAKLIK RSS" href="{{ url('/rss.xml') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        red: {!! json_encode($brandPalette) !!},
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
    <style>
        :root { --sultraklik-accent: {{ $accentColor }}; }
        body { font-family: 'Inter', sans-serif; }
        .font-serif-news { font-family: 'Merriweather', Georgia, serif; }
        .article-body { font-family: Georgia, 'Times New Roman', serif; font-size: 19px; line-height: 1.85; color: #1f2937; }
        .article-body p { margin-bottom: 1.5rem; }
        .article-body h2 { font-family: 'Merriweather', serif; font-size: 24px; font-weight: 900; margin: 2rem 0 1rem; }
        .article-body h3 { font-family: 'Merriweather', serif; font-size: 20px; font-weight: 700; margin: 1.5rem 0 0.75rem; }
        .article-body blockquote { border-left: 4px solid var(--sultraklik-accent); padding-left: 1rem; font-style: italic; color: #4b5563; margin: 1.5rem 0; }
        .article-body img { border-radius: 0.5rem; margin: 1.5rem 0; }
        .article-body a { color: var(--sultraklik-accent); text-decoration: underline; }
        .article-body ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 1.5rem; }
        .article-body ol { list-style: decimal; padding-left: 1.5rem; margin-bottom: 1.5rem; }
        .article-body figure figcaption { font-size: 13px; color: #6b7280; }
        .article-body table { border-collapse: collapse; margin: 1.5rem 0; }
        .article-body td, .article-body th { border: 1px solid #d1d5db; padding: 0.5rem 0.75rem; }
    </style>
</head>
<body class="bg-gray-100 text-gray-900 antialiased">
    {{-- Topbar --}}
    <div class="bg-red-700 text-white text-xs">
        <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between">
            <span class="font-semibold tracking-wide">SULAWESI TENGGARA &bull; {{ \Illuminate\Support\Carbon::now('Asia/Makassar')->translatedFormat('l, d F Y') }}</span>
            <div class="hidden md:flex items-center gap-4">
                <a href="{{ \App\Models\Setting::get('social.facebook') }}" class="hover:underline">Facebook</a>
                <a href="{{ \App\Models\Setting::get('social.instagram') }}" class="hover:underline">Instagram</a>
                <a href="{{ \App\Models\Setting::get('social.twitter') }}" class="hover:underline">X</a>
                <a href="{{ \App\Models\Setting::get('social.youtube') }}" class="hover:underline">YouTube</a>
            </div>
        </div>
    </div>

    {{-- Header / Logo --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <button type="button" class="md:hidden p-2 -ml-2 text-gray-700" x-data @click="$store.mobileMenu = !($store.mobileMenu ?? false)">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <a href="{{ url('/') }}" class="flex items-center">
                <span class="font-black text-3xl md:text-4xl tracking-tight font-serif-news">
                    SULTRA<span class="text-red-600">KLIK</span>
                </span>
            </a>
            <form action="{{ url('/search') }}" method="GET" class="hidden md:flex items-center gap-2">
                <div class="relative">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari berita..." class="border border-gray-300 rounded-full pl-4 pr-10 py-2 text-sm w-64 focus:outline-none focus:ring-2 focus:ring-red-500">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                    </button>
                </div>
            </form>
            <a href="{{ url('/search') }}" class="md:hidden p-2 text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            </a>
        </div>

        {{-- Navbar --}}
        <nav class="border-t border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4">
                <ul class="hidden md:flex items-center text-sm font-semibold overflow-x-auto">
                    <li><a href="{{ url('/') }}" class="block px-3 py-3 text-red-600 border-b-2 border-red-600 hover:bg-gray-50 whitespace-nowrap">BERANDA</a></li>
                    <li class="relative group">
                        <a href="{{ route('sultra') }}" class="block px-3 py-3 hover:bg-gray-50 whitespace-nowrap">
                            SULTRA
                            <svg class="inline w-3 h-3 ml-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </a>
                        <div class="absolute left-0 top-full hidden group-hover:block bg-white shadow-lg border border-gray-100 rounded-md min-w-[240px] z-50 py-1">
                            @foreach($regionsForNav ?? \App\Models\Region::where('is_active', true)->orderBy('order_column')->get() as $region)
                                <a href="{{ url('/sultra/'.$region->slug) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700">{{ $region->name }}</a>
                            @endforeach
                        </div>
                    </li>
                    @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->get() as $category)
                        <li><a href="{{ url('/kategori/'.$category->slug) }}" class="block px-3 py-3 hover:bg-gray-50 whitespace-nowrap">{{ strtoupper($category->name) }}</a></li>
                    @endforeach
                    <li><a href="{{ url('/search') }}" class="block px-3 py-3 hover:bg-gray-50"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg></a></li>
                </ul>
            </div>
        </nav>

        {{-- Mobile menu --}}
        <div x-show="$store.mobileMenu" x-cloak x-transition class="md:hidden bg-white border-t border-gray-200 shadow-lg">
            <div class="px-4 py-3 space-y-1 max-h-[70vh] overflow-y-auto">
                <a href="{{ url('/') }}" class="block py-2 font-semibold text-red-600">BERANDA</a>
                <a href="{{ route('sultra') }}" class="block py-2 font-semibold border-b border-gray-100">SULTRA</a>
                @foreach(\App\Models\Region::where('is_active', true)->orderBy('order_column')->get() as $region)
                    <a href="{{ url('/sultra/'.$region->slug) }}" class="block py-1.5 pl-4 text-sm text-gray-600">— {{ $region->name }}</a>
                @endforeach
                @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->get() as $category)
                    <a href="{{ url('/kategori/'.$category->slug) }}" class="block py-2 font-semibold">{{ strtoupper($category->name) }}</a>
                @endforeach
            </div>
        </div>
    </header>

    {{-- Breaking news --}}
    @php($breaking = $breakingNews ?? \App\Models\BreakingNews::live()->first())
    @if($breaking)
        <div class="bg-gray-900 text-white">
            <div class="max-w-7xl mx-auto px-4 py-2 flex items-center gap-3 overflow-hidden">
                <span class="shrink-0 bg-red-600 text-white text-xs font-bold px-2.5 py-1 rounded flex items-center gap-1.5 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-white inline-block"></span>
                    BREAKING NEWS
                </span>
                <a href="{{ $breaking->url ?: '#' }}" class="truncate text-sm font-medium hover:underline" @if($breaking->url) target="_blank" @endif>{{ $breaking->title }}</a>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('mobileMenu', false);
        });
    </script>
    <style>[x-cloak] { display: none !important; }</style>

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-950 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-2">
                <p class="font-black text-2xl font-serif-news text-white">SULTRA<span class="text-red-500">KLIK</span></p>
                <p class="mt-3 text-sm leading-relaxed max-w-md">{{ \App\Models\Setting::get('general.description') }}</p>
                <p class="mt-3 text-xs text-gray-500">{{ \App\Models\Setting::get('general.address') }}</p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-3 text-sm tracking-wider">KATEGORI</h4>
                <ul class="space-y-2 text-sm">
                    @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->take(8)->get() as $category)
                        <li><a href="{{ url('/kategori/'.$category->slug) }}" class="hover:text-red-400">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-3 text-sm tracking-wider">INFORMASI</h4>
                <ul class="space-y-2 text-sm">
                    @foreach(\App\Models\Page::where('is_active', true)->get() as $page)
                        <li><a href="{{ url('/page/'.$page->slug) }}" class="hover:text-red-400">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-4 text-xs text-gray-500 flex flex-col md:flex-row justify-between gap-2">
                <span>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('general.name') }}. All rights reserved.</span>
                <span><a href="{{ url('/sitemap.xml') }}" class="hover:text-red-400">Sitemap</a> &bull; <a href="{{ url('/rss.xml') }}" class="hover:text-red-400">RSS</a> &bull; <a href="{{ url('/page/kontak') }}" class="hover:text-red-400">Kontak</a></span>
            </div>
        </div>
    </footer>
</body>
</html>