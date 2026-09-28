@props(['title', 'url' => null, 'dark' => false])

<div class="flex items-center justify-between mb-5 border-b-2 {{ $dark ? 'border-white/10' : 'border-gray-200' }}">
    <h2 class="relative pb-2 font-black text-base md:text-lg tracking-widest uppercase {{ $dark ? 'text-white' : 'text-gray-900' }}">
        <span class="absolute bottom-[-2px] left-0 h-[3px] w-14 bg-accent-600"></span>
        {{ $title }}
    </h2>
    @if($url)
        <a href="{{ $url }}" class="inline-flex items-center justify-center w-8 h-8 rounded-full border transition-colors {{ $dark ? 'border-white/20 text-white hover:bg-accent-600 hover:border-accent-600' : 'border-gray-300 text-gray-500 hover:text-white hover:bg-accent-600 hover:border-accent-600' }}" title="Lihat Semua" aria-label="Lihat Semua">
            <i class="fa-solid fa-arrow-right"></i>
        </a>
    @endif
</div>