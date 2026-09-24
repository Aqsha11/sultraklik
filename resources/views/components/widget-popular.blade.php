@props(['title' => 'TERPOPULER', 'articles' => []])

<div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2.5 border-b-2 border-gray-200">
        <h3 class="relative pb-1 font-black text-sm tracking-widest uppercase text-gray-900">
            <span class="absolute bottom-[-2px] left-0 h-[3px] w-8 bg-red-600"></span>
            {{ $title }}
        </h3>
        <i class="fa-solid fa-fire text-red-500"></i>
    </div>
    <ul class="divide-y divide-gray-100">
        @forelse($articles as $i => $article)
            <li>
                <a href="{{ url($article->slug) }}" class="group flex items-stretch gap-3 p-3 hover:bg-gray-50 transition-colors">
                    <span class="font-black text-2xl font-serif-news {{ $i < 3 ? 'text-red-600' : 'text-gray-300' }} shrink-0 self-center">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    @if($article->featured_image_url)
                        <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" loading="lazy" class="w-16 h-12 object-cover rounded shrink-0 self-center">
                    @else
                        <div class="w-16 h-12 bg-gray-200 rounded shrink-0 self-center flex items-center justify-center text-[9px] font-bold text-gray-400">SULTRAKLIK</div>
                    @endif
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