@props(['position', 'height' => 'h-32', 'maxWidth' => null, 'imgHeight' => null])

@php($align = $maxWidth ? $maxWidth . ' mx-auto' : '')
@php($imgClass = $imgHeight ? "w-full {$imgHeight} object-cover object-center" : 'w-full h-auto object-contain mx-auto')

<div class="w-full">
    @php($ads = \App\Models\Advertisement::active($position)->get())
    @forelse($ads as $ad)
        @if($ad->type === 'image' && $ad->image_url)
            @if($ad->url)
                <a href="{{ $ad->url }}" target="_blank" rel="noopener" class="block bg-gray-100 {{ $align }}">
                    <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="{{ $imgClass }}">
                </a>
            @else
                <div class="bg-gray-100 {{ $align }}">
                    <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="{{ $imgClass }}">
                </div>
            @endif
        @elseif($ad->code)
            {!! $ad->code !!}
        @endif
    @empty
        <div class="w-full {{ $height }} flex items-center justify-center border-2 border-dashed border-gray-300 text-xs font-bold text-gray-400 uppercase tracking-wider select-none">
            Iklan {{ \App\Filament\Resources\AdvertisementResource::POSITIONS[$position] ?? $position }}
        </div>
    @endforelse
</div>