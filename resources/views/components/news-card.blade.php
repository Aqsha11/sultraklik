@props(['article', 'showImage' => true, 'excerpt' => false, 'textSize' => 'text-base'])

<article class="flex bg-white rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow h-full">
    @if($showImage)
        <div class="shrink-0 w-32 md:w-44">
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
    <div class="flex-1 min-w-0 p-3 md:p-4">
        <div class="flex items-center gap-2 mb-1">
            @if($article->region)
                <a href="{{ url('/sultra/'.$article->region->slug) }}" class="text-[11px] font-bold text-red-600 uppercase tracking-wide hover:underline">{{ $article->region->name }}</a>
            @elseif($article->category)
                <a href="{{ url('/kategori/'.$article->category->slug) }}" class="text-[11px] font-bold text-red-600 uppercase tracking-wide hover:underline">{{ $article->category->name }}</a>
            @endif
            <span class="text-[11px] text-gray-500">{{ $article->published_at->diffForHumans() }}</span>
        </div>
        <a href="{{ url($article->slug) }}">
            <h3 class="font-semibold {{ $textSize }} leading-snug hover:text-red-700 line-clamp-2 transition-colors">{{ $article->title }}</h3>
        </a>
        @if($excerpt && $article->excerpt)
            <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $article->excerpt }}</p>
        @endif

        <x-card-actions :article="$article" />
    </div>
</article>