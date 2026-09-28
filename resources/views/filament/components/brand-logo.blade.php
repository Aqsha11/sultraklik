@php
    $brandSiteName = \App\Models\Setting::get('general.name', 'SULTRAKLIK');
    $brandLogoPath = \App\Models\Setting::get('theme.logo');
    $brandHasLogo = $brandLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($brandLogoPath);
@endphp

@if($brandHasLogo)
    <img src="{{ Storage::disk('public')->url($brandLogoPath) }}" alt="{{ $brandSiteName }}" class="h-full w-auto max-w-full object-contain">
@else
    <span class="text-xl font-bold leading-5 tracking-tight text-gray-950 dark:text-white">{{ $brandSiteName }}</span>
@endif