<x-layouts.app title="Beranda">
    <div class="max-w-7xl mx-auto px-4 py-6">

        {{-- HEADLINE UTAMA + PENDAMPING --}}
        @if($headlineMain?->article)
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
                <div class="lg:col-span-2 bg-white rounded-lg overflow-hidden shadow-sm">
                    <a href="{{ url($headlineMain->article->slug) }}" class="group block">
                        @if($headlineMain->article->featured_image_url)
                            <img src="{{ $headlineMain->article->featured_image_url }}" alt="{{ $headlineMain->article->title }}" class="w-full aspect-[16/9] object-cover">
                        @else
                            <div class="w-full aspect-[16/9] bg-gradient-to-br from-gray-300 to-gray-500 flex items-center justify-center text-white text-2xl font-black font-serif-news">SULTRAKLIK</div>
                        @endif
                    </a>
                    <div class="p-5">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-red-600 text-white text-[11px] font-bold px-2 py-0.5 rounded uppercase">Headline</span>
                            @if($headlineMain->article->region)
                                <a href="{{ url('/sultra/'.$headlineMain->article->region->slug) }}" class="text-xs font-bold text-red-600 uppercase">{{ $headlineMain->article->region->name }}</a>
                            @else
                                <a href="{{ url('/kategori/'.$headlineMain->article->category->slug) }}" class="text-xs font-bold text-red-600 uppercase">{{ $headlineMain->article->category->name }}</a>
                            @endif
                        </div>
                        <a href="{{ url($headlineMain->article->slug) }}">
                            <h1 class="font-serif-news font-black text-2xl md:text-3xl lg:text-4xl leading-tight group-hover:text-red-700 transition-colors">{{ $headlineMain->article->title }}</h1>
                        </a>
                        <p class="text-sm text-gray-500 mt-3">{{ $headlineMain->article->published_at->diffForHumans() }} &bull; {{ $headlineMain->article->views }} views</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-1 gap-4">
                    @foreach($headlineOthers as $article)
                        <x-news-card-vertical :article="$article" showCategory="true" textSize="text-sm text-base" />
                    @endforeach
                </div>
            </div>
        @endif

        {{-- BERITA TERBARU + TERPOPULER --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="bg-gray-900 text-white px-3 py-2 font-bold text-sm tracking-wider uppercase rounded">Berita Terbaru</h2>
                    <button type="button" class="flex items-center gap-1.5 text-red-600 text-2xl font-black" x-data="{ live: false }" @click="live = !live">
                        <svg x-show="live" class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 4v5h.582M20 20v-5h-.581M4.582 9A9 9 0 0119.419 4.57M20 15a9 9 0 01-14.83 5.43"/></svg>
                        <svg x-show="!live" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    @forelse($latest as $article)
                        <x-news-card :article="$article" showImage="true" textSize="text-lg" />
                    @empty
                        <div class="bg-white p-8 text-center text-gray-500">Belum ada berita terbaru.</div>
                    @endforelse
                </div>
            </div>

            <aside class="space-y-6">
                <x-widget-popular :articles="$popular" />
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <x-section-heading title="BERITA WILAYAH" />
                    <ul class="grid grid-cols-2 gap-2">
                        @foreach($regions as $region)
                            <li>
                                <a href="{{ url('/sultra/'.$region->slug) }}" class="flex justify-between items-center px-3 py-2 text-sm font-semibold bg-gray-50 hover:bg-red-50 hover:text-red-700 rounded transition-colors">
                                    {{ $region->name }}
                                    <span class="text-xs text-gray-400">{{ $region->articles_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>

        {{-- Section per kategori --}}
        @foreach($sections as $slug => $articles)
            @if($articles->count())
                <div class="mt-12">
                    <x-section-heading title="{{ strtoupper(\App\Models\Category::where('slug', $slug)->value('name') ?? $slug) }}" url="{{ url('/kategori/'.$slug) }}" />
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($articles as $article)
                            <x-news-card-vertical :article="$article" textSize="text-sm" />
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        {{-- SULTRA regions strip --}}
        <div class="mt-12 bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="bg-gray-900 px-4 py-2.5 flex items-center justify-between">
                <h2 class="text-white font-bold text-sm tracking-wider uppercase">SULAWESI TENGGARA</h2>
                <a href="{{ route('sultra') }}" class="text-xs text-red-400 font-semibold hover:text-red-300">Semua &rarr;</a>
            </div>
            <div class="p-4">
                <div class="flex flex-wrap gap-2">
                    @foreach($regions->take(12) as $region)
                        <a href="{{ url('/sultra/'.$region->slug) }}" class="px-3 py-1.5 bg-gray-100 hover:bg-red-600 hover:text-white text-sm font-semibold rounded-full transition-colors">{{ $region->name }}</a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>