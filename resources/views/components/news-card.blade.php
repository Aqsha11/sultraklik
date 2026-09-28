@props(['article', 'showImage' => true, 'excerpt' => false, 'textSize' => 'text-base'])

<article class="flex flex-col md:flex-row bg-white overflow-hidden shadow-sm hover:shadow-md transition-shadow h-full">
    @if($showImage)
        {{-- Mobile / Bootstrap-style overlay card --}}
        <a href="{{ url($article->slug) }}" class="relative block md:hidden aspect-[4/3] overflow-hidden group">
            @if($article->featured_image_url)
                <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy"
                     class="absolute inset-0 w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
            @else
                <div class="w-full h-full bg-gradient-to-br from-gray-300 to-gray-500 flex items-center justify-center text-white font-black text-sm">SULTRAKLIK</div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/35 to-black/10">
                <span class="absolute top-2 left-2 bg-accent-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded uppercase tracking-wide">
                    {{ $article->region?->name ?? $article->category?->name }}
                </span>
                <div class="absolute inset-x-0 bottom-0 p-2.5 pt-10">
                    <h3 class="text-white font-bold text-[13px] leading-snug line-clamp-2 group-hover:text-accent-400 transition-colors">{{ $article->title }}</h3>
                    <p class="text-[11px] text-gray-300 mt-1"><i class="fa-regular fa-clock"></i> {{ $article->published_at->diffForHumans() }}</p>
                </div>
            </div>
        </a>
        <div class="md:hidden bg-gray-900 px-2.5 py-2">
            <x-card-actions-overlay :article="$article" />
        </div>

        {{-- Desktop image --}}
        <div class="hidden md:block shrink-0 w-32 md:w-44">
            <a href="{{ url($article->slug) }}" class="block w-full h-full">
                @if($article->featured_image_url)
                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy"
                         class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full min-h-[96px] bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-400">SULTRAKLIK</div>
                @endif
            </a>
        </div>
    @endif

    <div class="hidden md:flex md:flex-col flex-1 min-w-0 p-3 md:p-4">
        <div class="flex items-center gap-2 mb-1">
            @if($article->region)
                <a href="{{ url('/sultra/'.$article->region->slug) }}" class="text-[11px] font-bold text-accent-600 uppercase tracking-wide hover:underline">{{ $article->region->name }}</a>
            @elseif($article->category)
                <a href="{{ url('/kategori/'.$article->category->slug) }}" class="text-[11px] font-bold text-accent-600 uppercase tracking-wide hover:underline">{{ $article->category->name }}</a>
            @endif
            <span class="text-[11px] text-gray-500">{{ $article->published_at->diffForHumans() }}</span>
        </div>
        <a href="{{ url($article->slug) }}">
            <h3 class="font-semibold {{ $textSize }} leading-snug hover:text-accent-700 line-clamp-2 transition-colors">{{ $article->title }}</h3>
        </a>
        @if($excerpt && $article->excerpt)
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $article->excerpt }}</p>
        @endif

        <x-card-actions :article="$article" />
    </div>
</article>