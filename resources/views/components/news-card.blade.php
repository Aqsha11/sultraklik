@props(['article', 'showImage' => true, 'excerpt' => false, 'textSize' => 'text-base'])

<x-news-card-link :article="$article">
    <article class="flex gap-3 bg-white">
        @if($showImage && $article->featured_image_url)
            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy"
                 class="w-28 h-20 md:w-32 md:h-24 object-cover rounded-md shrink-0">
        @endif
        <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1">
                @if($article->region)
                    <a href="{{ url('/sultra/'.$article->region->slug) }}" class="text-[11px] font-bold text-red-600 uppercase tracking-wide hover:underline">{{ $article->region->name }}</a>
                @elseif($article->category)
                    <a href="{{ url('/kategori/'.$article->category->slug) }}" class="text-[11px] font-bold text-red-600 uppercase tracking-wide hover:underline">{{ $article->category->name }}</a>
                @endif
                <span class="text-[11px] text-gray-500">{{ $article->published_at->diffForHumans() }}</span>
            </div>
            <h3 class="font-semibold {{ $textSize }} leading-snug hover:text-red-700 line-clamp-2 transition-colors">{{ $article->title }}</h3>
            @if($excerpt && $article->excerpt)
                <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $article->excerpt }}</p>
            @endif
        </div>
    </article>
</x-news-card-link>