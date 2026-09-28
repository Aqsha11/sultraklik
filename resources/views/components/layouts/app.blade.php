@props([
    'title' => null,
    'metaDescription' => null,
    'metaKeywords' => null,
    'metaImage' => null,
    'article' => null,
    'breakingNews' => null,
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

    $defaultMetaTitle = \App\Models\Setting::get('seo.meta_title');
    $defaultMetaDescription = \App\Models\Setting::get('seo.meta_description');
    $documentTitle = filled($title) ? $title.' — '.$siteName : ($defaultMetaTitle ?: $siteName);
    $metaDescriptionValue = filled($metaDescription)
        ? $metaDescription
        : ($defaultMetaDescription ?: \App\Models\Setting::get('general.tagline', 'Portal Berita Sulawesi Tenggara'));
    $metaKeywordsValue = filled($metaKeywords) ? $metaKeywords : \App\Models\Setting::get('seo.meta_keywords');

    // State aktif navbar. Tanpa ini link BERANDA selalu tampil aktif karena
    // styling hardcode. Kategori ikut tersorot saat artikelnya dibaca, memakai
    // prop $article yang dikirim articles/show.
    $navActiveCategory = $article?->category?->slug;
    $navIsHome = request()->is('/');
    $navIsSultra = request()->is('sultra') || request()->is('sultra/*') || $navActiveCategory === 'sultra';
    $navIsSearch = request()->is('search') || request()->is('tag/*');
    $navIsCategory = fn ($slug) => $navActiveCategory === $slug
        || request()->is('kategori/'.$slug)
        || request()->is('category/'.$slug);
    $navIsPage = fn ($slug) => request()->is('page/'.$slug);

    // Link navbar desktop / drawer. Kelas aktif memakai skala `red` yang di-
    // override layout ke theme.primary_color, jadi otomatis ikut brand.
    $navClasses = fn (bool $active) => $active
        ? 'block px-3 py-3 whitespace-nowrap text-red-600 border-b-2 border-red-600'
        : 'block px-3 py-3 whitespace-nowrap hover:bg-gray-50';
    $navClassesDrawer = fn (bool $active) => $active
        ? 'block py-2 font-semibold text-red-600'
        : 'block py-2 font-semibold';
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('sultraklik-theme');
                var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>

    <title>{{ $documentTitle }}</title>
    <meta name="description" content="{{ $metaDescriptionValue }}">
    @if(filled($metaKeywordsValue))
        <meta name="keywords" content="{{ $metaKeywordsValue }}">
    @endif

    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $documentTitle }}">
    <meta property="og:description" content="{{ $metaDescriptionValue }}">
    <meta property="og:locale" content="id_ID">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
    <meta property="og:type" content="{{ $article ? 'article' : 'website' }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $documentTitle }}">
    <meta name="twitter:description" content="{{ $metaDescriptionValue }}">
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
            'publisher' => ['@type' => 'Organization', 'name' => $siteName],
            'description' => $article->excerpt,
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES) !!}
        </script>
    @endif

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" type="application/rss+xml" title="{{ $siteName }} RSS" href="{{ url('/rss.xml') }}">
    @if($siteFavicon)
        <link rel="icon" href="{{ Storage::disk('public')->url($siteFavicon) }}">
        <link rel="apple-touch-icon" href="{{ Storage::disk('public')->url($siteFavicon) }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        red: {!! json_encode($brandPalette) !!},
                        accent: {!! json_encode(\App\Support\ColorPalette::shades($accentColor)) !!},
                    },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/night.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=Merriweather:wght@700;900&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        :root { --sultraklik-primary: {{ $brandColor }}; --sultraklik-accent: {{ $accentColor }}; }
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
        /* Ticker breaking news: satu salinan penuh = 50% lebar track, jadi
           menggeser -50% menaruh salinan kedua tepat di posisi salinan pertama. */
        .breaking-marquee { animation: breaking-marquee var(--breaking-duration, 30s) linear infinite; }
        .breaking-marquee:hover { animation-play-state: paused; }
        @keyframes breaking-marquee { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @media (prefers-reduced-motion: reduce) { .breaking-marquee { animation: none; } }
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
                <a href="{{ \App\Models\Setting::get('social.tiktok', '#') }}" class="hover:underline" title="TikTok" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
            </div>
        </div>
    </div>

    {{-- Header / Logo. Header + breaking news dibungkus satu container sticky
         supaya keduanya nempel sebagai satu blok di offset 0 yang sama. --}}
    <div class="sticky top-0 z-40">
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="relative max-w-7xl mx-auto px-4 py-3 flex items-center gap-4">
            <button type="button" class="md:hidden p-2 -ml-2 text-gray-700 shrink-0" x-data @click="$store.mobileMenu = !($store.mobileMenu ?? false)">
                <i class="fa-solid fa-bars text-xl"></i>
            </button>
            {{-- Mobile: dipusatkan absolut terhadap baris, bukan lewat flex-1, karena
                 hamburger (44px) lebih sempit dari cluster kanan (~100px) sehingga
                 flex-1 selalu menggeser logo ke kiri. --}}
            <a href="{{ url('/') }}" aria-label="{{ $siteName }}" class="flex items-center justify-center md:justify-start min-w-0 shrink-0 max-w-[7.5rem] sm:max-w-[9rem] md:max-w-[10rem] absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 md:static md:translate-x-0 md:translate-y-0">
                @if($siteLogo)
                    <img src="{{ Storage::disk('public')->url($siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto max-w-[10rem] object-contain shrink-0">
                @else
                    <span class="font-black text-xl md:text-2xl tracking-tight font-serif-news uppercase text-gray-900 truncate">{{ $siteName }}</span>
                @endif
            </a>
            <div class="flex items-center gap-2 md:gap-3 ml-auto shrink-0">
                <form action="{{ url('/search') }}" method="GET" class="hidden md:block relative" x-data="liveSearch()" x-ref="form">
                    <div>
                        <input type="text" name="q" x-model="query" @input.debounce.300ms="search" @focus="open = true" @click.outside="open = false" @keydown.escape="open = false" placeholder="Cari berita..." autocomplete="off" class="w-64 border border-gray-300 rounded-full pl-4 pr-10 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        <button type="submit" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-500 hover:text-accent-600">
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
                <button type="button" x-data="themeToggle" @click="toggle()" class="shrink-0 flex h-10 w-10 items-center justify-center rounded-full border border-gray-300 text-gray-600 transition-colors hover:bg-gray-50 hover:text-accent-600" :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'" :title="dark ? 'Mode Terang' : 'Mode Gelap'">
                    <i x-show="! dark" class="fa-solid fa-moon"></i>
                    <i x-show="dark" x-cloak class="fa-solid fa-sun text-accent-600"></i>
                </button>
            </div>
        </div>

        {{-- Navbar --}}
        <nav class="border-t border-gray-200 bg-white">
            <div class="max-w-7xl mx-auto px-4 flex items-center">
                <ul class="hidden md:flex items-center text-sm font-semibold shrink-0">
                    <li><a href="{{ url('/') }}" class="{{ $navClasses($navIsHome) }}">BERANDA</a></li>
                    <li class="relative" x-data="{ sultraOpen: false }" @click.outside="sultraOpen = false">
                        <button type="button" @click="sultraOpen = !sultraOpen" class="flex items-center gap-1.5 px-3 py-3 whitespace-nowrap cursor-pointer {{ $navIsSultra ? 'text-red-600 border-b-2 border-red-600' : 'hover:bg-gray-50' }}">
                            SULTRA
                            <i x-bind:class="sultraOpen ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-[10px] transition-transform"></i>
                        </button>
                        <div x-show="sultraOpen" x-cloak x-transition class="absolute left-0 top-full bg-white shadow-lg border border-gray-100 rounded-md min-w-[260px] z-50 py-1">
                            <a href="{{ route('sultra') }}" @click="sultraOpen = false" class="block px-4 py-2 text-sm font-semibold text-gray-900 hover:bg-accent-50 hover:text-accent-700">Semua Wilayah Sultra</a>
                            @foreach($regionsForNav ?? \App\Models\Region::where('is_active', true)->orderBy('order_column')->get() as $region)
                                <a href="{{ url('/sultra/'.$region->slug) }}" @click="sultraOpen = false" class="block px-4 py-2 text-sm text-gray-700 hover:bg-accent-50 hover:text-accent-700">{{ $region->name }}</a>
                            @endforeach
                        </div>
                    </li>
                </ul>
                <ul class="hidden md:flex items-center text-sm font-semibold overflow-x-auto min-w-0">
                    @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->get() as $category)
                        <li><a href="{{ url('/kategori/'.$category->slug) }}" class="{{ $navClasses($navIsCategory($category->slug)) }}">{{ strtoupper($category->name) }}</a></li>
                    @endforeach
                    @foreach($topbarPages as $navPage)
                        <li><a href="{{ url('/page/'.$navPage->slug) }}" class="{{ $navClasses($navIsPage($navPage->slug)) }}">{{ strtoupper($navPage->title) }}</a></li>
                    @endforeach
                    <li><a href="{{ url('/search') }}" class="block px-3 py-3 {{ $navIsSearch ? 'border-b-2 border-red-600' : 'hover:bg-gray-50' }}"><i class="fa-solid fa-magnifying-glass text-sm {{ $navIsSearch ? 'text-red-600' : 'text-gray-500' }}"></i></a></li>
                </ul>
            </div>
        </nav>

        {{-- Backdrop mobile menu --}}
        <div x-show="$store.mobileMenu" x-cloak x-data
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
            @click="$store.mobileMenu = false"
            class="fixed inset-0 z-40 bg-black/50 md:hidden"></div>

        {{-- Drawer mobile menu dari kiri --}}
        <div x-data x-show="$store.mobileMenu" x-cloak
            x-effect="document.body.style.overflow = $store.mobileMenu ? 'hidden' : ''"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
            class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] bg-white shadow-2xl md:hidden flex flex-col">
            <div class="relative flex items-center justify-center px-4 py-3 border-b border-gray-200 bg-red-700 text-white shrink-0">
                @php($drawerLogo = \App\Models\Setting::get('theme.logo'))
                @php($drawerName = \App\Models\Setting::get('general.name', 'SULTRAKLIK'))
                <a href="{{ url('/') }}" @click="$store.mobileMenu = false" class="flex items-center">
                    @if($drawerLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($drawerLogo))
                        <img src="{{ Storage::disk('public')->url($drawerLogo) }}" alt="{{ $drawerName }}" class="h-10 w-auto max-w-[10rem] object-contain filter drop-shadow">
                    @else
                        <span class="font-black tracking-wide font-serif-news text-lg uppercase">{{ $drawerName }}</span>
                    @endif
                </a>
                <button type="button" @click="$store.mobileMenu = false" class="absolute right-2 top-1/2 -translate-y-1/2 p-2" title="Tutup" aria-label="Tutup menu">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>
            <div class="flex-1 px-4 py-3 space-y-1 overflow-y-auto">
                <a href="{{ url('/') }}" @click="$store.mobileMenu = false" class="{{ $navClassesDrawer($navIsHome) }}">BERANDA</a>
                <div x-data="{ sultraOpen: false }">
                    <a href="{{ route('sultra') }}" @click.prevent="sultraOpen = !sultraOpen" class="flex items-center justify-between py-2 font-semibold border-b border-gray-100 cursor-pointer {{ $navIsSultra ? 'text-red-600' : '' }}">
                        <span>SULTRA</span>
                        <i x-bind:class="sultraOpen ? 'rotate-180' : ''" class="fa-solid fa-chevron-down text-xs text-gray-500 transition-transform"></i>
                    </a>
                    <div x-show="sultraOpen" x-cloak x-transition class="pt-1">
                        @foreach(\App\Models\Region::where('is_active', true)->orderBy('order_column')->get() as $region)
                            <a href="{{ url('/sultra/'.$region->slug) }}" @click="$store.mobileMenu = false" class="block py-1.5 pl-4 text-sm {{ request()->is('sultra/'.$region->slug) ? 'text-red-600 font-semibold' : 'text-gray-600' }}">— {{ $region->name }}</a>
                        @endforeach
                    </div>
                </div>
                @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->get() as $category)
                    <a href="{{ url('/kategori/'.$category->slug) }}" @click="$store.mobileMenu = false" class="{{ $navClassesDrawer($navIsCategory($category->slug)) }}">{{ strtoupper($category->name) }}</a>
                @endforeach
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Halaman</p>
                    @foreach($topbarPages as $navPage)
                        <a href="{{ url('/page/'.$navPage->slug) }}" @click="$store.mobileMenu = false" class="block py-1.5 text-sm {{ $navIsPage($navPage->slug) ? 'text-red-600 font-semibold' : 'text-gray-600' }}">{{ $navPage->title }}</a>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 mt-2 pt-2">
                    <button type="button" x-data="themeToggle" @click="toggle()" class="flex w-full items-center justify-between py-2 font-semibold" :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'">
                        <span x-text="dark ? 'Mode Terang' : 'Mode Gelap'">Mode Gelap</span>
                        <i x-show="! dark" class="fa-solid fa-moon text-sm text-gray-500"></i>
                        <i x-show="dark" x-cloak class="fa-solid fa-sun text-sm text-accent-600"></i>
                    </button>
                </div>
                <div class="border-t border-gray-100 mt-2 pt-3 pb-4 flex items-center gap-3">
                    <a href="{{ \App\Models\Setting::get('social.facebook') }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center hover:opacity-90" title="Facebook" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="{{ \App\Models\Setting::get('social.instagram') }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-gradient-to-tr from-purple-600 via-pink-500 to-yellow-400 text-white flex items-center justify-center hover:opacity-90" title="Instagram" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="{{ \App\Models\Setting::get('social.twitter') }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center hover:opacity-90" title="X" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="{{ \App\Models\Setting::get('social.youtube') }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center hover:opacity-90" title="YouTube" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    <a href="{{ \App\Models\Setting::get('social.tiktok', '#') }}" target="_blank" rel="noopener" class="w-8 h-8 rounded-full bg-gray-900 text-white flex items-center justify-center hover:opacity-90" title="TikTok" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>
        </div>
    </header>

    {{-- Breaking news: marquee kontinu, semua item live tampil dalam satu bar --}}
    @php($breakingItems = $breakingNews ?? \App\Models\BreakingNews::live()->get())
    @php($breakingDuration = max(18, $breakingItems->count() * 9))
    @if($breakingItems->isNotEmpty())
    <div class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 py-1.5 sm:py-2 flex items-center gap-2 sm:gap-3">
            <span class="shrink-0 bg-[#dc2626] text-white text-[10px] sm:text-xs font-bold px-1.5 sm:px-2.5 py-0.5 sm:py-1 rounded flex items-center gap-1 sm:gap-1.5 animate-pulse">
                <span class="w-1.5 h-1.5 sm:w-2 sm:h-2 rounded-full bg-white inline-block"></span>
                BREAKING NEWS
            </span>

            <div class="flex-1 min-w-0 overflow-hidden">
                @if($breakingItems->count() > 1)
                <div class="breaking-marquee flex w-max" style="--breaking-duration: {{ $breakingDuration }}s">
                    {{-- Dua salinan identik supaya translateX(-50%) mengulang tanpa jeda --}}
                    @foreach([1, 2] as $copy)
                    <div class="flex shrink-0 items-center" @if($copy === 2) aria-hidden="true" @endif>
                        @foreach($breakingItems as $item)
                        <a href="{{ $item->url ?: '#' }}" @if($item->url) target="_blank" @endif rel="noopener noreferrer" class="whitespace-nowrap px-2 sm:px-3 first:pl-0 text-xs sm:text-sm font-medium hover:underline">{{ $item->title }}</a>
                        <span class="text-white/30 select-none" aria-hidden="true">&bull;</span>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @else
                <div class="flex items-center">
                    <a href="{{ $breakingItems->first()->url ?: '#' }}" @if($breakingItems->first()->url) target="_blank" @endif rel="noopener noreferrer" class="truncate text-xs sm:text-sm font-medium hover:underline">{{ $breakingItems->first()->title }}</a>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
    </div>{{-- /sticky header + breaking news --}}

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

        function themeToggle() {
            return {
                dark: document.documentElement.getAttribute('data-theme') === 'dark',
                toggle() {
                    this.dark = !this.dark;
                    if (this.dark) {
                        document.documentElement.setAttribute('data-theme', 'dark');
                    } else {
                        document.documentElement.removeAttribute('data-theme');
                    }
                    try {
                        localStorage.setItem('sultraklik-theme', this.dark ? 'dark' : 'light');
                    } catch (e) {}
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
                <div class="flex items-start gap-4">
                    @if($siteLogo)
                        <div class="logo-plate bg-white shrink-0 rounded-lg px-2 py-1.5">
                            <img src="{{ Storage::disk('public')->url($siteLogo) }}" alt="{{ $siteName }}" class="h-10 w-auto max-w-[10rem] object-contain">
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-sm leading-relaxed">{{ \App\Models\Setting::get('general.description') }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ \App\Models\Setting::get('general.address') }}</p>
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-white font-bold mb-3 text-sm tracking-wider">KATEGORI</h4>
                <ul class="space-y-2 text-sm">
                    @foreach(\App\Models\Category::where('is_active', true)->where('slug', '!=', 'sultra')->orderBy('order_column')->take(8)->get() as $category)
                        <li><a href="{{ url('/kategori/'.$category->slug) }}" class="hover:text-accent-400">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-3 text-sm tracking-wider">INFORMASI</h4>
                <ul class="space-y-2 text-sm">
                    @foreach(\App\Models\Page::where('is_active', true)->get() as $page)
                        <li><a href="{{ url('/page/'.$page->slug) }}" class="hover:text-accent-400">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800">
            <div class="max-w-7xl mx-auto px-4 py-4 text-xs text-gray-500 flex flex-col md:flex-row justify-between gap-2">
                <span>&copy; {{ date('Y') }} {{ \App\Models\Setting::get('general.name') }}. All rights reserved.</span>
                <a href="https://viteks.id/" target="_blank" rel="noopener nofollow" class="inline-flex items-center gap-2 transition-opacity hover:opacity-80" title="Viteks &mdash; Virtual Teknologi Studio">
                    <span>Powered by</span>
                    {{-- Varian terang: logo asli navy #041f60 tidak terbaca di
                         footer bg-gray-950 tanpa latar putih. --}}
                    <img src="{{ asset('images/viteks-logo-light.png') }}" alt="Viteks" class="h-5 w-auto object-contain" loading="lazy">
                </a>
            </div>
        </div>
    </footer>

    {{-- Tombol kembali ke atas --}}
    <button type="button" x-data="{ show: false }" x-init="window.addEventListener('scroll', () => { show = (window.scrollY > 300); }, { passive: true })"
        x-show="show" x-transition x-cloak @click="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="fixed bottom-5 right-4 z-50 w-11 h-11 rounded-full bg-red-600 text-white shadow-lg flex items-center justify-center hover:bg-red-700 transition-colors"
        title="Kembali ke atas" aria-label="Kembali ke atas">
        <i class="fa-solid fa-chevron-up"></i>
    </button>
    </div>
</body>
</html>