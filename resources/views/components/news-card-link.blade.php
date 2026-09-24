@props(['article'])

@php($href = url($article->slug))
<a href="{{ $href }}" class="group block">
    {{ $slot }}
</a>