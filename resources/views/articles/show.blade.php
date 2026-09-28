<x-layouts.app
    :title="$article->seo_title ?: $article->title"
    :meta-description="$article->seo_description ?: ($article->excerpt ?: null)"
    :meta-keywords="$article->tags->pluck('name')->join(', ')"
    :meta-image="$article->seo_image"
    :article="$article"
>
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                {{-- Breadcrumb --}}
                <nav class="text-xs text-gray-500 mb-3 flex flex-wrap items-center gap-1">
                    <a href="{{ url('/') }}" class="hover:text-accent-600">BERANDA</a>
                    &raquo;
                    @if($article->region)
                        <a href="{{ url('/sultra') }}" class="hover:text-accent-600">SULTRA</a>
                        &raquo;
                        <a href="{{ url('/sultra/'.$article->region->slug) }}" class="hover:text-accent-600">{{ $article->region->name }}</a>
                    @elseif($article->category)
                        <a href="{{ url('/kategori/'.$article->category->slug) }}" class="hover:text-accent-600">{{ strtoupper($article->category->name) }}</a>
                    @endif
                </nav>

                <article>
                    <h1 class="font-serif-news font-black text-2xl md:text-4xl leading-tight mb-3">{{ $article->title }}</h1>

                    <div class="flex items-center justify-between border-b border-gray-200 pb-3 mb-5">
                        <div class="flex items-center gap-2">
                            <div class="w-9 h-9 rounded-full bg-red-600 text-white flex items-center justify-center font-bold text-sm">
                                {{ strtoupper(substr($article->author?->name ?? 'S', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-bold">{{ $article->author?->name }}</p>
                                <p class="text-xs text-gray-500">{{ $article->published_at->translatedFormat('d F Y') }} &bull; {{ $article->published_at->format('H:i') }} WITA</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <button type="button" x-data="cardLike({{ $article->id }}, {{ $article->likes_count ?? 0 }}, '{{ $article->slug }}')" @click="toggle()" :class="liked ? 'text-accent-600' : 'hover:text-accent-600'" class="flex items-center gap-1.5 text-xs font-bold text-gray-500 transition-colors" title="Suka" aria-label="Suka">
                                <i :class="liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-sm"></i>
                                <span x-text="likes"></span>
                            </button>
                            <span class="flex items-center gap-1.5 text-xs font-bold text-gray-500" title="{{ number_format($article->views) }} views" aria-label="{{ number_format($article->views) }} views">
                                <i class="fa-regular fa-eye"></i>
                                {{ number_format($article->views) }}
                            </span>
                        </div>
                    </div>

                    @if($article->hasVideo())
                        <figure class="mb-5" x-data="{ played: false }">
                            <div class="relative aspect-[16/9] overflow-hidden rounded-lg bg-gray-900">
                                <button type="button" x-show="!played" @click="played = true" class="group absolute inset-0 h-full w-full" aria-label="Putar video YouTube">
                                    <img src="{{ $article->video_thumbnail_url }}" alt="Thumbnail video: {{ $article->title }}" loading="lazy" class="h-full w-full object-cover">
                                    <span class="absolute inset-0 bg-black/25 transition group-hover:bg-black/40"></span>
                                    <span class="absolute inset-0 m-auto flex h-16 w-16 items-center justify-center rounded-full bg-red-600 text-white shadow-lg transition group-hover:scale-105 group-hover:bg-red-700">
                                        <i class="fa-solid fa-play ml-1 text-2xl"></i>
                                    </span>
                                    <span class="absolute bottom-2 left-2 rounded bg-black/70 px-2 py-0.5 text-xs font-bold text-white">Video</span>
                                </button>
                                <template x-if="played">
                                    <iframe :src="'{{ $article->video_embed_url }}?autoplay=1'" title="Video: {{ $article->title }}" class="absolute inset-0 h-full w-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen referrerpolicy="strict-origin-when-cross-origin"></iframe>
                                </template>
                            </div>
                            <figcaption class="mt-2 text-xs text-gray-500">Video YouTube &bull; diputar di situs ini, tanpa membuka YouTube.</figcaption>
                        </figure>
                    @endif

                    @if($article->featured_image_url)
                        <figure class="mb-5">
                            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-full rounded-lg">
                            @if($article->image_caption)
                                <figcaption class="text-xs text-gray-500 mt-2">{{ $article->image_caption }}@if($article->image_credit) <span class="italic">({{ $article->image_credit }})</span>@endif</figcaption>
                            @endif
                        </figure>
                    @endif

                    @if($article->excerpt)
                        <p class="text-lg font-serif text-gray-700 leading-relaxed italic mb-6">{{ $article->excerpt }}</p>
                    @endif

                    <div class="article-body">
                        {!! \App\Support\HtmlSanitizer::clean($article->content) !!}
                    </div>

                    {{-- Share --}}
                    <div class="flex items-center gap-2 mt-8 pt-5 border-t border-gray-200">
                        <span class="text-sm font-bold text-gray-700 mr-1">Bagikan:</span>
                        @php($shareUrl = url()->current())
                        @php($shareText = urlencode($article->title))
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center w-9 h-9 bg-blue-600 hover:bg-blue-700 text-white rounded" title="Bagikan ke Facebook" aria-label="Bagikan ke Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://api.whatsapp.com/send?text={{ $shareText }}%20{{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center w-9 h-9 bg-green-600 hover:bg-green-700 text-white rounded" title="Bagikan ke WhatsApp" aria-label="Bagikan ke WhatsApp">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ urlencode($shareUrl) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center w-9 h-9 bg-gray-900 hover:bg-gray-800 text-white rounded" title="Bagikan ke X" aria-label="Bagikan ke X">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                    </div>

                    {{-- Tags --}}
                    @if($article->tags->count())
                        <div class="mt-6">
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Tags:</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($article->tags as $tag)
                                    <a href="{{ url('/tag/'.$tag->slug) }}" class="px-3 py-1 bg-gray-100 hover:bg-accent-600 hover:text-white text-xs font-semibold rounded-full transition-colors">#{{ $tag->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>

                {{-- Komentar --}}
                <section id="komentar" class="mt-10 scroll-mt-24">
                    <x-section-heading title="Komentar ({{ $comments->count() }})" />

                    @if(session('comment_status'))
                        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm">
                            <i class="fa-solid fa-circle-check mr-1.5"></i>{{ session('comment_status') }}
                        </div>
                    @endif

                    @error('name')
                        <div class="bg-accent-50 border border-accent-200 text-accent-700 px-4 py-3 rounded-lg mb-4 text-sm">
                            @foreach($errors->all() as $error)
                                <p><i class="fa-solid fa-circle-exclamation mr-1.5"></i>{{ $error }}</p>
                            @endforeach
                        </div>
                    @enderror

                    <form method="POST" action="{{ route('comment.store', $article->slug) }}" class="bg-white border border-gray-200 rounded-lg p-5 mb-8">
                        @csrf
                        {{-- Honeypot anti-bot (harus dibiarkan kosong oleh manusia) --}}
                        <div class="hidden" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden">
                            <input type="hidden" name="formed_at" value="{{ time() }}">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent-500">
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (opsional)"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-accent-500">
                        </div>
                        <textarea name="body" rows="4" placeholder="Tulis komentar Anda..." required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-accent-500">{{ old('body') }}</textarea>

                        {{-- Token dikirim widget sebagai input cf-turnstile-response,
                             jadi tidak perlu field manual di sini. --}}
                        <x-turnstile action="comment" />

                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold px-4 py-2.5 rounded-md transition-colors">
                            <i class="fa-solid fa-paper-plane mr-1.5"></i>Kirim Komentar
                        </button>
                    </form>

                    <div class="space-y-4">
                        @forelse($comments as $comment)
                            <div class="bg-white border border-gray-200 rounded-lg p-4">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="w-8 h-8 rounded-full bg-gray-800 text-white flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($comment->name ?? 'U', 0, 1)) }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-bold">{{ $comment->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $comment->created_at->translatedFormat('d F Y') }} &bull; {{ $comment->created_at->format('H:i') }} WITA</p>
                                    </div>
                                    @if($comment->user_id)
                                        <span class="ml-auto text-[10px] font-bold text-accent-600 uppercase bg-accent-50 px-2 py-0.5 rounded">Redaksi</span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $comment->body }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center py-6">Belum ada komentar. Jadilah yang pertama memberi komentar.</p>
                        @endforelse
                    </div>
                </section>
            </div>

            <aside class="space-y-6">
                <x-widget-popular title="TERPOPULER" :articles="\App\Models\Article::published()->orderByDesc('views')->limit(5)->get()" />
                <x-sidebar-ads position="sidebar" />
            </aside>
        </div>

        {{-- Berita Terkait --}}
        @if($related->count())
            <div class="mt-12" x-data>
                <x-section-heading title="BERITA TERKAIT" />

                {{-- Mobile: carousel gaya Pilihan Sultra Klik --}}
                <div class="relative group/scroll md:hidden">
                    <div class="flex overflow-x-auto snap-x snap-mandatory gap-4 pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden scroll-smooth" x-ref="relatedTrack">
                        @foreach($related as $i => $article)
                            <x-news-card-link :article="$article">
                                <article class="relative overflow-hidden min-w-[260px] flex-shrink-0 snap-start group">
                                    @if($article->featured_image_url)
                                        <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy" class="w-full aspect-[4/3] object-cover transition-transform duration-300 group-hover:scale-105">
                                    @else
                                        <div class="w-full aspect-[4/3] bg-gray-800 flex items-center justify-center text-gray-500 font-black text-lg">SULTRAKLIK</div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent flex flex-col justify-end p-4">
                                        <h5 class="text-white font-bold text-sm leading-snug line-clamp-2 transition-colors group-hover:text-accent-400">{{ $article->title }}</h5>
                                        <p class="text-xs text-gray-300 mt-1.5">{{ $article->region?->name ?? $article->category?->name }} &bull; {{ $article->published_at->diffForHumans() }}</p>
                                    </div>
                                </article>
                            </x-news-card-link>
                        @endforeach
                    </div>

                    <button type="button" @click="$refs.relatedTrack.scrollBy({ left: -280, behavior: 'smooth' })"
                        class="absolute left-0 top-1/2 -translate-y-1/2 bg-white shadow-md rounded-full p-2 text-gray-700 hover:bg-accent-600 hover:text-white transition-colors -ml-4 border border-gray-100">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" @click="$refs.relatedTrack.scrollBy({ left: 280, behavior: 'smooth' })"
                        class="absolute right-0 top-1/2 -translate-y-1/2 bg-white shadow-md rounded-full p-2 text-gray-700 hover:bg-accent-600 hover:text-white transition-colors -mr-4 border border-gray-100">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>

                {{-- Desktop: grid 2 kolom --}}
                <div class="hidden md:grid md:grid-cols-2 gap-3 md:gap-4">
                    @foreach($related as $article)
                        <x-news-card :article="$article" textSize="text-lg" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>