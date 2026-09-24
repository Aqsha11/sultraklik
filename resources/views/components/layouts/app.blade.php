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
    $siteName = \App\Models\Setting::get('general.name', 'SULTRAKLIK');
    $siteLogo = \App\Models\Setting::get('theme.logo');
    $siteFavicon = \App\Models\Setting::get('theme.favicon');
    $siteShareImage = \App\Models\Setting::get('theme.share_image') ?: $siteLogo;
    $ogImage = $metaImage ?: $siteShareImage;
    if ($ogImage && ! \Illuminate\Support\Str::startsWith(\Illuminate\Support\Str::lower($ogImage), ['http://', 'https://'])) {
        $ogImage = url('storage/'.$ogImage);
    }
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
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:type" content="{{ $article ? 'article' : 'website' }}">
    <meta name="twitter:card" content="summary_large_image">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif
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
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS" href="{{ url('/rss.xml') }}">
    @if($siteFavicon)
        <link rel="icon" href="{{ asset('storage/'.$siteFavicon) }}">
        <link rel="apple-touch-icon" href="{{ asset('storage/'.$siteFavicon) }}">
    @endif

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
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
<body class="bg-gray-100 text-gray-900 antialiased min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col">
    {{-- Topbar --}}
    <div class="bg-red-700 text-white text-xs">
        <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between">
            <span class="font-semibold tracking-wide">{{ \Illuminate\Support\Carbon::now('Asia/Makassar')->translatedFormat('l, d F Y') }}</span>
            <div class="hidden md:flex items-center gap-4">
                @php($topbarPages = \App\Models\Page::where('is_active', true)->orderBy('title')->get())
                @foreach($topbarPages as $topbarPage)
                    <a href="{{ url('/page/'.$topbarPage->slug) }}" class="hover:underline">{{ strtoupper($topbarPage->title) }}</a>
                @endforeach
                <span class="text-white/40">|</span>
                <a href="{{ \App\Models\Setting::get('social.facebook') }}" class="hover:underline" title="Facebook" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="{{ \App\Models\Setting::get('social.instagram') }}" class="hover:underline" title="Instagram" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                <a href="{{ \App\Models\Setting::get('social.twitter') }}" class="hover:underline" title="X" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="{{ \App\Models\Setting::get('social.youtube') }}" class="hover:underline" title="YouTube" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
            </div>
        </div>
    </div>

    {{-- Header / Logo --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-4">
            <button type="button" class="md:hidden p-2 -ml-2 text-gray-700" x-data @click="$store.mobileMenu = !($store.mobileMenu ?? false)">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            <a href="{{ url('/') }}" class="flex items-center">
                @if($siteLogo)
                    <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $siteName }}" class="h-11 md:h-12 w-auto object-contain">
                @else
                    <span class="font-black text-3xl md:text-4xl tracking-tight font-serif-news">
                        SULTRA<span class="text-red-600">KLIK</span>
                    </span>
                @endif
            </a>
            <form action="{{ url('/search') }}" method="GET" class="hidden md:block relative" x-data="liveSearch()" x-ref="form">
                <div>
                    <input type="text" name="q" x-model="query" @input.debounce.300ms="search" @focus="open = true" @click.outside="open = false" @keydown.escape="open = false" placeholder="Cari berita..." autocomplete="off" class="w-64 border border-gray-300 rounded-full pl-4 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                    <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-600">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                <div x-show="open && query.length >= 2 && !loading && results.length" x-transition x-cloak class="absolute left-0 right-0 top-full mt-2 z-50 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-xl">
                    <template x-for="r in results" :key="r.slug">
                        <a :href="'/' + r.slug" class="flex items-center gap-3 px-3 py-2.5 hover:bg-gray-50" @click="open = false">
                            <img :src="r.image" alt="" class="h-9 w-12 shrink-0 rounded object-cover bg-gray-100">
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-semibold text-gray-900" x-text="r.title"></span>
                                <span class="block truncate text-xs text-gray-500" x-text="r.label"></span>
                            </span>
                        </a>
                    </template>
                </div>

                <div x-show="open && query.length >= 2 && !loading && !results.length" x-cloak class="absolute left-0 right-0 top-full mt-2 z-50 rounded-lg border border-gray-200 bg-white p-4 text-center text-sm text-gray-500 shadow-xl">
                    Tidak ada berita ditemukan.
                </div>
            </form>
            <a href="{{ url('/search') }}" class="md:hidden p-2 text-gray-700">
                <i class="fa-solid fa-magnifying-glass text-xl"></i>
            </a>
        </div>

        {{-- Navbar --}}
        <nav class="border-t border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 flex items-center">
                <ul class="hidden md:flex items-center text-sm font-semibold shrink-0">
                    <li><a href="{{ url('/') }}" class="block px-3 py-3 text-red-600 border-b-2 border-red-600 hover:bg-gray-50 whitespace-nowrap">BERANDA</a></li>
                    <li class="relative" x-data="{ sultraOpen: false }" @click.outside="sultraOpen = false">
                        <button type="button" @click="sultraOpen = !sultraOpen" class="flex items-center gap-1.5 px-3 py-3 hover:bg-gray-50 whitespace-nowrap cursor-pointer">
                            SULTRA
                            <i x-bind:class="sultraOpen ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-[10px] transition-transform"></i>
                        </button>
                        <div x-show="sultraOpen" x-cloak x-transition class="absolute left-0 top-full bg-white shadow-lg border border-gray-100 rounded-md min-w-[260px] z-50 py-1">
                            <a href="{{ route('sultra') }}" @click="sultraOpen = false" class="block px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-red-50 hover:text-red-700">Semua Wilayah Sultra</a>
                            @foreach($regionsForNav ?? \App\Models\Region::where('is_active', true)->orderBy('order_column')->get() as $region)
                                <a href="{{ url('/sultra/'.$region->slug) }}" @click="sultraOpen = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700">{{ $region->name }}</a>
                            @endforeach
                        </div>
                    </li>
                </ul>
                <ul class="hidden md:flex items-center text-sm font-semibold overflow-x-auto min-w-0">
                    @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->get() as $category)
                        <li><a href="{{ url('/kategori/'.$category->slug) }}" class="block px-3 py-3 hover:bg-gray-50 whitespace-nowrap">{{ strtoupper($category->name) }}</a></li>
                    @endforeach
                    <li><a href="{{ url('/search') }}" class="block px-3 py-3 hover:bg-gray-50"><i class="fa-solid fa-magnifying-glass text-sm text-gray-500"></i></a></li>
                </ul>
            </div>
        </nav>

        {{-- Mobile menu --}}
        <div x-show="$store.mobileMenu" x-cloak x-transition class="md:hidden bg-white border-t border-gray-200 shadow-lg">
            <div class="px-4 py-3 space-y-1 max-h-[70vh] overflow-y-auto">
                <a href="{{ url('/') }}" class="block py-2 font-semibold text-red-600">BERANDA</a>
                <div x-data="{ sultraOpen: false }">
                    <a href="{{ route('sultra') }}" @click.prevent="sultraOpen = !sultraOpen" class="flex items-center justify-between py-2 font-semibold border-b border-gray-100 cursor-pointer">
                        <span>SULTRA</span>
                        <i x-bind:class="sultraOpen ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform"></i>
                    </a>
                    <div x-show="sultraOpen" x-cloak x-transition class="pt-1">
                        @foreach(\App\Models\Region::where('is_active', true)->orderBy('order_column')->get() as $region)
                            <a href="{{ url('/sultra/'.$region->slug) }}" class="block py-1.5 pl-4 text-sm text-gray-600">— {{ $region->name }}</a>
                        @endforeach
                    </div>
                </div>
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
                <span class="shrink-0 bg-[#dc2626] text-white text-xs font-bold px-2.5 py-1 rounded flex items-center gap-1.5 animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-white inline-block"></span>
                    BREAKING NEWS
                </span>
                <a href="{{ $breaking->url ?: '#' }}" class="truncate text-sm font-medium hover:underline" @if($breaking->url) target="_blank" @endif>{{ $breaking->title }}</a>
            </div>
        </div>
    @endif

    <script>
        function liveSearch() {
            return {
                query: '{{ request('q') }}',
                results: [],
                open: false,
                loading: false,
                async search() {
                    const q = this.query.trim();
                    if (q.length < 2) {
                        this.results = [];
                        this.open = false;
                        return;
                    }
                    this.loading = true;
                    try {
                        const res = await fetch('/search/live?q=' + encodeURIComponent(q));
                        const data = await res.json();
                        this.results = (data || []).map((r) => ({
                            ...r,
                            label: [r.category, r.region, r.published_at].filter(Boolean).join(' \u2022 '),
                        }));
                        this.open = true;
                    } catch (e) {
                        this.results = [];
                    }
                    this.loading = false;
                },
            };
        }

        function homeLoadMore(section, initialIds) {
            return {
                ids: initialIds || [],
                loading: false,
                hasMore: true,
                async load() {
                    if (this.loading || !this.hasMore) return;
                    this.loading = true;
                    try {
                        const res = await fetch('/home/load-more?section=' + section + '&exclude=' + this.ids.join(','));
                        const data = await res.json();
                        if (data.html) {
                            this.$refs.grid.insertAdjacentHTML('beforeend', data.html);
                            if (window.Alpine) Alpine.initTree(this.$refs.grid);
                            this.ids = this.ids.concat((data.ids || []).filter((x) => !this.ids.includes(x)));
                        }
                        this.hasMore = !!data.hasMore;
                    } catch (e) {
                        this.hasMore = false;
                    }
                    this.loading = false;
                },
            };
        }

        document.addEventListener('alpine:init', () => {
            Alpine.store('mobileMenu', false);
        });

        function cardLike(articleId, initialLikes, slug) {
            return {
                likes: initialLikes,
                liked: false,
                busy: false,
                async toggle() {
                    if (this.busy) return;
                    this.busy = true;
                    const token = document.querySelector('meta[name="csrf-token"]');
                    try {
                        const res = await fetch('/artikel/' + slug + '/like', {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token ? token.content : '' },
                        });
                        const data = await res.json();
                        this.likes = data.likes;
                        this.liked = data.liked;
                    } catch (e) {}
                    this.busy = false;
                },
            };
        }

        function cardShare(title, slug) {
            return {
                open: false,
                copied: false,
                url: window.location.origin + '/' + slug,
                title: title,
                get fb() {
                    return 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(this.url);
                },
                get wa() {
                    return 'https://api.whatsapp.com/send?text=' + encodeURIComponent(this.title + ' ' + this.url);
                },
                get tw() {
                    return 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(this.title) + '&url=' + encodeURIComponent(this.url);
                },
                async copy() {
                    try {
                        await navigator.clipboard.writeText(this.url);
                        this.copied = true;
                        setTimeout(() => (this.copied = false), 1500);
                    } catch (e) {}
                },
            };
        }
    </script>
    <style>[x-cloak] { display: none !important; }</style>

    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="bg-gray-950 text-gray-300 mt-12">
        <div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-4 gap-8">
            <div class="col-span-2">
                @if($siteLogo)
                    <img src="{{ asset('storage/'.$siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto object-contain mb-3">
                @else
                    <p class="font-black text-2xl font-serif-news text-white">SULTRA<span class="text-red-500">KLIK</span></p>
                @endif
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
    </div>
</body>
</html>