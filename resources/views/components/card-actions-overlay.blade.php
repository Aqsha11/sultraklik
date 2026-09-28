@props(['article'])

<div class="flex items-center gap-3 mt-2 text-white">
    <button type="button" x-data="cardLike({{ $article->id }}, {{ $article->likes_count ?? 0 }}, '{{ $article->slug }}')" @click="toggle()" :class="liked ? 'text-accent-400' : 'hover:text-accent-400'" class="flex items-center gap-1 text-xs transition-colors" title="Suka" aria-label="Suka">
        <i :class="liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-xs"></i>
        <span x-text="likes" class="text-[10px] font-semibold"></span>
    </button>
    <a href="{{ url($article->slug.'#komentar') }}" class="flex items-center gap-1 text-xs hover:text-accent-400 transition-colors" title="Komentar" aria-label="Komentar">
        <i class="fa-regular fa-comment text-xs"></i>
        <span class="text-[10px] font-semibold">{{ number_format($article->comments_count) }}</span>
    </a>
    <span class="flex items-center gap-1 text-xs" title="Dilihat" aria-label="Dilihat">
        <i class="fa-regular fa-eye text-xs"></i>
        <span class="text-[10px] font-semibold">{{ number_format($article->views) }}</span>
    </span>
    <div class="relative ml-auto" x-data="cardShare('{{ addslashes($article->title) }}', '{{ $article->slug }}')" @click.outside="open = false">
        <button type="button" @click="open = !open" class="flex items-center justify-center w-6 h-6 rounded-full hover:bg-white/20 transition-colors" title="Bagikan" aria-label="Bagikan">
            <i class="fa-solid fa-share-nodes text-xs"></i>
        </button>
        <div x-show="open" x-cloak x-transition class="absolute right-0 top-full mt-1 z-50 min-w-[150px] rounded-lg border border-gray-200 bg-white p-2 shadow-xl">
            <div class="flex items-center gap-2">
                <a :href="fb" target="_blank" rel="noopener" class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a :href="wa" target="_blank" rel="noopener" class="flex items-center justify-center w-8 h-8 rounded-full bg-green-600 text-white hover:bg-green-700 transition-colors" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <a :href="tw" target="_blank" rel="noopener" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-900 text-white hover:bg-gray-800 transition-colors" title="X"><i class="fa-brands fa-x-twitter"></i></a>
                <button type="button" @click="copy()" class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:text-accent-600 hover:border-accent-600 transition-colors" :title="copied ? 'Tersalin!' : 'Salin link'" aria-label="Salin link">
                    <i :class="copied ? 'fa-solid fa-check text-green-600' : 'fa-regular fa-copy'"></i>
                </button>
            </div>
        </div>
    </div>
</div>