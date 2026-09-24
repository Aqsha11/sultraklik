<x-layouts.app
    :title="$article->title"
    :meta-description="$article->seo_description ?? $article->excerpt"
    :meta-image="$article->seo_image"
    :article="$article"
>
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                {{-- Breadcrumb --}}
                <nav class="text-xs text-gray-500 mb-3 flex flex-wrap items-center gap-1">
                    <a href="{{ url('/') }}" class="hover:text-red-600">BERANDA</a>
                    &raquo;
                    @if($article->region)
                        <a href="{{ url('/sultra') }}" class="hover:text-red-600">SULTRA</a>
                        &raquo;
                        <a href="{{ url('/sultra/'.$article->region->slug) }}" class="hover:text-red-600">{{ $article->region->name }}</a>
                    @elseif($article->category)
                        <a href="{{ url('/kategori/'.$article->category->slug) }}" class="hover:text-red-600">{{ strtoupper($article->category->name) }}</a>
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
                        <span class="text-xs text-gray-400 hidden md:block">{{ number_format($article->views) }} views</span>
                    </div>

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
                        {!! $article->content !!}
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
                                    <a href="{{ url('/tag/'.$tag->slug) }}" class="px-3 py-1 bg-gray-100 hover:bg-red-600 hover:text-white text-xs font-semibold rounded-full transition-colors">#{{ $tag->name }}</a>
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
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4 text-sm">
                            @foreach($errors->all() as $error)
                                <p><i class="fa-solid fa-circle-exclamation mr-1.5"></i>{{ $error }}</p>
                            @endforeach
                        </div>
                    @enderror

                    <form method="POST" action="{{ route('comment.store', $article->slug) }}" class="bg-white border border-gray-200 rounded-lg p-5 mb-8">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                            <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama" required
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email (opsional)"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
                        </div>
                        <textarea name="body" rows="4" placeholder="Tulis komentar Anda..." required
                            class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm mb-3 focus:outline-none focus:ring-2 focus:ring-red-500">{{ old('body') }}</textarea>
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
                                        <span class="ml-auto text-[10px] font-bold text-red-600 uppercase bg-red-50 px-2 py-0.5 rounded">Redaksi</span>
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
            <div class="mt-12">
                <x-section-heading title="BERITA TERKAIT" />
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($related as $article)
                        <x-news-card :article="$article" textSize="text-lg" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>