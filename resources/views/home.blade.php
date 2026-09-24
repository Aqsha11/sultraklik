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
                        <x-card-actions :article="$headlineMain->article" />
                    </div>
                </div>

                <div class="flex flex-col gap-4 lg:h-full lg:overflow-y-auto lg:pr-1">
                    @foreach($headlineOthers as $article)
                        <x-news-card :article="$article" showImage="true" textSize="text-sm" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-10">
            <x-ad-slot position="homepage_top" height="h-16" imgHeight="h-16" />
        </div>

        {{-- BERITA TERBARU + TRENDING + KATEGORI + SIDEBAR --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-12">
                <div x-data="homeLoadMore('latest', @json($pageUsedIds))">
                    <div class="flex items-center justify-between mb-5 border-b-2 border-gray-200">
                        <h2 class="relative pb-2 font-black text-base md:text-lg tracking-widest uppercase text-gray-900">
                            <span class="absolute bottom-[-2px] left-0 h-[3px] w-14 bg-red-600"></span>
                            Berita Terbaru
                        </h2>
                        <button type="button" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 text-gray-500 hover:text-white hover:bg-red-600 hover:border-red-600 transition-colors" x-data="{ live: false }" @click="live = !live" title="Segarkan" aria-label="Segarkan">
                            <i x-show="live" class="fa-solid fa-rotate animate-spin"></i>
                            <i x-show="!live" class="fa-solid fa-rotate-right"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-ref="grid">
                        @forelse($latest as $article)
                            <x-news-card :article="$article" showImage="true" textSize="text-lg" />
                        @empty
                            <div class="bg-white md:col-span-2 p-8 text-center text-gray-500">Belum ada berita terbaru.</div>
                        @endforelse
                    </div>
                    @if($latest->count())
                        <button type="button" x-show="hasMore" @click="load()" :disabled="loading" title="Muat Lebih Banyak" aria-label="Muat Lebih Banyak"
                            class="mt-6 w-full flex items-center justify-center">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full border-2 border-gray-300 bg-white text-gray-500 hover:text-red-600 hover:border-red-600 transition-colors disabled:opacity-60">
                                <i x-show="!loading" class="fa-solid fa-chevron-down"></i>
                                <i x-show="loading" class="fa-solid fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    @endif
                </div>

                {{-- TRENDING --}}
                @if($popular->count())
                    <div>
                        <x-section-heading title="TRENDING" />
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach($popular->take(3) as $i => $article)
                                <x-news-card-link :article="$article">
                                    <article class="relative overflow-hidden">
                                        @if($article->featured_image_url)
                                            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy" class="w-full aspect-[4/3] object-cover transition-transform duration-300 group-hover:scale-105">
                                        @else
                                            <div class="w-full aspect-[4/3] bg-gray-800 flex items-center justify-center text-gray-500 font-black text-lg">SULTRAKLIK</div>
                                        @endif
                                        <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-4">
                                            <span class="text-white font-black text-xl leading-none mb-1">{{ '#'.($i + 1) }}</span>
                                            <h5 class="text-white font-bold text-sm leading-snug line-clamp-2 transition-colors group-hover:text-red-400">{{ $article->title }}</h5>
                                            <p class="text-xs text-gray-300 mt-1.5">{{ $article->region?->name ?? $article->category?->name }} &bull; <i class="fa-solid fa-fire text-red-400"></i> {{ number_format($article->views) }} dibaca</p>
                                        </div>
                                    </article>
                                </x-news-card-link>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- PILIHAN SULTRAKLIK --}}
                @if($picked->count())
                    <div x-data>
                        <x-section-heading title="Pilihan Sultra Klik" />
                        <div class="relative group/scroll">
                            <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden scroll-smooth" x-ref="pickedTrack">
                                @foreach($picked as $i => $article)
                                    <x-news-card-link :article="$article">
                                        <article class="relative overflow-hidden min-w-[260px] md:min-w-[300px] flex-shrink-0 snap-start group">
                                            @if($article->featured_image_url)
                                                <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy" class="w-full aspect-[4/3] object-cover transition-transform duration-300 group-hover:scale-105">
                                            @else
                                                <div class="w-full aspect-[4/3] bg-gray-800 flex items-center justify-center text-gray-500 font-black text-lg">SULTRAKLIK</div>
                                            @endif
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-4">
                                                <h5 class="text-white font-bold text-sm leading-snug line-clamp-2 transition-colors group-hover:text-red-400">{{ $article->title }}</h5>
                                                <p class="text-xs text-gray-300 mt-1.5">{{ $article->region?->name ?? $article->category?->name }} &bull; {{ $article->published_at->diffForHumans() }}</p>
                                            </div>
                                        </article>
                                    </x-news-card-link>
                                @endforeach
                            </div>

                            <button type="button" @click="$refs.pickedTrack.scrollBy({ left: -320, behavior: 'smooth' })"
                                class="absolute left-0 top-1/2 -translate-y-1/2 bg-white shadow-md rounded-full p-2 text-gray-700 hover:bg-red-600 hover:text-white transition-colors -ml-4 border border-gray-100">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>
                            <button type="button" @click="$refs.pickedTrack.scrollBy({ left: 320, behavior: 'smooth' })"
                                class="absolute right-0 top-1/2 -translate-y-1/2 bg-white shadow-md rounded-full p-2 text-gray-700 hover:bg-red-600 hover:text-white transition-colors -mr-4 border border-gray-100">
                                <i class="fa-solid fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- REKOMENDASI UNTUK ANDA --}}
                @if($recommended->count())
                    <div x-data="homeLoadMore('recommend', @json($pageUsedIds))">
                        <x-section-heading title="Rekomendasi Untuk Anda" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-ref="grid">
                            @foreach($recommended as $article)
                                <x-news-card :article="$article" textSize="text-lg" />
                            @endforeach
                        </div>
                        <button type="button" x-show="hasMore" @click="load()" :disabled="loading" title="Muat Lebih Banyak" aria-label="Muat Lebih Banyak"
                            class="mt-6 w-full flex items-center justify-center">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full border-2 border-gray-300 bg-white text-gray-500 hover:text-red-600 hover:border-red-600 transition-colors disabled:opacity-60">
                                <i x-show="!loading" class="fa-solid fa-chevron-down"></i>
                                <i x-show="loading" class="fa-solid fa-spinner fa-spin"></i>
                            </span>
                        </button>
                    </div>
                @endif

                {{-- WILAYAH SULTRA --}}
                @if($wilayahArticles->count())
                    <div>
                        <x-section-heading title="Wilayah Sultra" />
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($wilayahArticles as $article)
                                <x-news-card :article="$article" textSize="text-lg" />
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <aside class="space-y-6">
                <x-sidebar-ads position="sidebar" />
            </aside>
        </div>
    </div>
</x-layouts.app>