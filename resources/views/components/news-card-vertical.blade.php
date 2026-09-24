@props(['article', 'showCategory' => true, 'textSize' => 'text-base'])

<x-news-card-link :article="$article">
    <article class="bg-white overflow-hidden hover:shadow-md transition-shadow">
        @if($article->featured_image_url)
            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy" class="w-full aspect-[16/10] object-cover">
        @else
            <div class="w-full aspect-[16/10] bg-gray-200 flex items-center justify-center text-gray-400 font-bold text-sm">
                SULTRAKLIK
            </div>
        @endif
        <div class="p-3">
            <div class="flex items-center gap-2 mb-1">
                <span class="inline-block w-6 h-1 rounded bg-red-600"></span>
                @if($showCategory && $article->region)
                    <a href="{{ url('/sultra/'.$article->region->slug) }}" class="text-[11px] font-bold text-red-600 uppercase tracking-wide">{{ $article->region->name }}</a>
                @elseif($showCategory && $article->category)
                    <a href="{{ url('/kategori/'.$article->category->slug) }}" class="text-[11px] font-bold text-red-600 uppercase tracking-wide">{{ $article->category->name }}</a>
                @endif
            </div>
            <h3 class="font-bold {{ $textSize }} leading-snug hover:text-red-700 line-clamp-2 transition-colors">{{ $article->title }}</h3>
            <p class="text-xs text-gray-500 mt-2">{{ $article->published_at->diffForHumans() }}</p>
        </div>
    </article>
</x-news-card-link>