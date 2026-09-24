@props(['title', 'url' => null, 'color' => 'bg-red-600'])

<div class="flex items-center justify-between mb-4">
    <a href="{{ $url }}" @class([
        'inline-flex items-center gap-2 px-3 py-2 text-white font-bold text-sm tracking-wider uppercase rounded',
        $color,
    ])>
        {{ $title }}
    </a>
    @if($url)
        <a href="{{ $url }}" class="text-sm text-gray-600 hover:text-red-600 font-semibold">Lihat Semua &rarr;</a>
    @endif
</div>