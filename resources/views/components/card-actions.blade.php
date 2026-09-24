@props(['article'])

<div class="flex items-center gap-3 mt-3 pt-2.5 border-t border-gray-100 text-[11px] text-gray-500">
    <button type="button" x-data="cardLike({{ $article->id }}, {{ $article->likes_count ?? 0 }}, '{{ $article->slug }}')" @click="toggle()" :class="liked ? 'text-red-600' : 'hover:text-red-600'" class="flex items-center gap-1.5 font-semibold transition-colors" title="Suka" aria-label="Suka">
        <i :class="liked ? 'fa-solid fa-heart' : 'fa-regular fa-heart'" class="text-sm"></i>
        <span x-text="likes"></span>
    </button>
    <a href="{{ url($article->slug.'#komentar') }}" class="flex items-center gap-1.5 font-semibold hover:text-red-600 transition-colors" title="Komentar" aria-label="Komentar">
        <i class="fa-regular fa-comment text-sm"></i>
        <span>{{ number_format($article->comments_count) }}</span>
    </a>
    <div class="relative ml-auto" x-data="cardShare('{{ addslashes($article->title) }}', '{{ $article->slug }}')" @click.outside="open = false">
        <button type="button" @click="open = !open" class="flex items-center justify-center w-7 h-7 rounded-full hover:bg-gray-100 hover:text-red-600 transition-colors" title="Bagikan" aria-label="Bagikan">
            <i class="fa-solid fa-share-nodes text-sm"></i>
        </button>
        <div x-show="open" x-cloak x-transition class="absolute right-0 bottom-full mb-2 z-50 min-w-[150px] rounded-lg border border-gray-200 bg-white p-2 shadow-xl">
            <div class="flex items-center gap-2">
                <a :href="fb" target="_blank" rel="noopener" class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-600 text-white hover:bg-blue-700 transition-colors" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a :href="wa" target="_blank" rel="noopener" class="flex items-center justify-center w-8 h-8 rounded-full bg-green-600 text-white hover:bg-green-700 transition-colors" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
                <a :href="tw" target="_blank" rel="noopener" class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-900 text-white hover:bg-gray-800 transition-colors" title="X"><i class="fa-brands fa-x-twitter"></i></a>
                <button type="button" @click="copy()" class="flex items-center justify-center w-8 h-8 rounded-full border border-gray-300 text-gray-600 hover:text-red-600 hover:border-red-600 transition-colors" :title="copied ? 'Tersalin!' : 'Salin link'" aria-label="Salin link">
                    <i :class="copied ? 'fa-solid fa-check text-green-600' : 'fa-regular fa-copy'"></i>
                </button>
            </div>
        </div>
    </div>
</div>