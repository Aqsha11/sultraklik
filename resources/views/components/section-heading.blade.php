@props(['title', 'url' => null])

<div class="flex items-center justify-between mb-5 border-b-2 border-gray-200">
    <h2 class="relative pb-2 font-black text-base md:text-lg tracking-widest uppercase text-gray-900">
        <span class="absolute bottom-[-2px] left-0 h-[3px] w-14 bg-red-600"></span>
        {{ $title }}
    </h2>
    @if($url)
        <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 text-gray-500 hover:text-white hover:bg-red-600 hover:border-red-600 transition-colors" title="Lihat Semua" aria-label="Lihat Semua">
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    @endif
</div>