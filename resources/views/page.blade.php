<x-layouts.app :title="$page->title" :meta-description="$page->seo_description">
    <div class="max-w-3xl mx-auto px-4 py-10">
        <nav class="text-xs text-gray-500 mb-4">
            <a href="{{ url('/') }}" class="hover:text-red-600">BERANDA</a> &raquo; <span class="text-gray-700">{{ $page->title }}</span>
        </nav>
        <h1 class="font-serif-news font-black text-3xl mb-6 border-b border-gray-200 pb-4">{{ $page->title }}</h1>
        <div class="article-body">
            {!! $page->content !!}
        </div>
    </div>
</x-layouts.app>