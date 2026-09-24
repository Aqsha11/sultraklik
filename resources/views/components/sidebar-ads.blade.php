@props(['position' => 'sidebar'])

@php($ads = \App\Models\Advertisement::active($position)->get())

@if($ads->count())
    @if($ads->count() > 4)
        <div class="flex flex-col gap-4 max-h-[2400px] overflow-y-auto pr-1">
            @foreach($ads as $ad)
                <div class="w-full bg-gray-100 shrink-0">
                    @if($ad->type === 'image' && $ad->image_url)
                        @if($ad->url)
                            <a href="{{ $ad->url }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-auto object-contain mx-auto max-h-[540px]">
                            </a>
                        @else
                            <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-auto object-contain mx-auto max-h-[540px]">
                        @endif
                    @elseif($ad->code)
                        <div class="w-full">{!! $ad->code !!}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col gap-4">
            @foreach($ads as $ad)
                <div class="w-full bg-gray-100 shrink-0">
                    @if($ad->type === 'image' && $ad->image_url)
                        @if($ad->url)
                            <a href="{{ $ad->url }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-auto object-contain mx-auto max-h-[540px]">
                            </a>
                        @else
                            <img src="{{ $ad->image_url }}" alt="{{ $ad->title }}" loading="lazy" class="w-full h-auto object-contain mx-auto max-h-[540px]">
                        @endif
                    @elseif($ad->code)
                        <div class="w-full">{!! $ad->code !!}</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@else
    <div class="w-full h-64 flex items-center justify-center border-2 border-dashed border-gray-300 text-xs font-bold text-gray-400 uppercase tracking-wider select-none bg-gray-100">
        Iklan Sidebar
    </div>
@endif