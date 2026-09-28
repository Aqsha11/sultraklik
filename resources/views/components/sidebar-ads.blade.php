@props(['position' => 'sidebar'])

@php($ads = \App\Models\Advertisement::active($position)->get())

@if($ads->count())
    <div class="flex gap-4 overflow-x-auto snap-x snap-mandatory [scrollbar-width:none] [&::-webkit-scrollbar]:hidden lg:flex-col lg:gap-6 lg:overflow-x-hidden lg:overflow-y-auto lg:max-h-[calc(100vh-8rem)] lg:pr-1 lg:snap-none">
        @foreach($ads as $ad)
            <div class="w-56 md:w-72 shrink-0 lg:w-full lg:shrink bg-gray-100">
                @if($ad->type === 'image' && $ad->image_url)
                    @if($ad->url)
                        <a href="{{ $ad->url }}" target="_blank" rel="noopener" class="block">
                            <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-auto object-contain mx-auto">
                        </a>
                    @else
                        <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-auto object-contain mx-auto">
                    @endif
                @elseif($ad->code)
                    <div class="w-full">{!! $ad->code !!}</div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="w-full h-64 flex items-center justify-center border-2 border-dashed border-gray-300 text-xs font-bold text-gray-400 uppercase tracking-wider select-none bg-gray-100">
        Iklan Sidebar
    </div>
@endif