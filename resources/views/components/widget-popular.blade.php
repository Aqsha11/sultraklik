@props(['title' => 'TERPOPULER', 'articles' => []])

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="bg-gray-900 text-white px-4 py-2.5 flex items-center justify-between">
        <h3 class="font-bold text-sm tracking-wider">{{ $title }}</h3>
        <span class="w-2 h-2 rounded-full bg-red-500"></span>
    </div>
    <ul class="divide-y divide-gray-100">
        @forelse($articles as $i => $article)
            <li>
                <a href="{{ url($article->slug) }}" class="group flex gap-3 p-3 hover:bg-gray-50 transition-colors">
                    <span class="font-black text-2xl font-serif-news {{ $i < 3 ? 'text-red-600' : 'text-gray-300' }} shrink-0">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold leading-snug line-clamp-2 group-hover:text-red-700 transition-colors">{{ $article->title }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ number_format($article->views) }} views</p>
                    </div>
                </a>
            </li>
        @empty
            <li class="p-4 text-sm text-gray-500">Belum ada data.</li>
        @endforelse
    </ul>
</div>