<x-layouts.app :title="$title">
    <div class="max-w-7xl mx-auto px-4 py-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="font-serif-news font-black text-2xl md:text-3xl uppercase flex items-center gap-3">
                <span class="inline-block w-1.5 h-8 bg-red-600"></span>
                {{ $title }}
            </h1>
            @if(request('q'))
                <span class="text-sm text-gray-500">Hasil pencarian "{{ request('q') }}"</span>
            @endif
        </div>

        @if(isset($regions) && $regions->count())
            <div class="flex flex-wrap gap-2 mb-6">
                @foreach($regions as $region)
                    <a href="{{ url('/sultra/'.$region->slug) }}"
                       class="px-3 py-1.5 {{ request()->is('sultra/'.$region->slug) ? 'bg-red-600 text-white' : 'bg-white text-gray-700 hover:bg-red-50 hover:text-red-700' }} text-sm font-semibold border border-gray-200 rounded-full transition-colors">
                        {{ $region->name }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-4">
                @forelse($articles as $article)
                    <x-news-card :article="$article" textSize="text-lg" excerpt="true" />
                @empty
                    <div class="bg-white p-10 text-center text-gray-500 rounded-lg">Belum ada berita.</div>
                @endforelse
            </div>

            <aside class="space-y-6">
                <x-widget-popular title="TERPOPULER" :articles="\App\Models\Article::published()->orderByDesc('views')->limit(5)->get()" />
            </aside>
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    </div>
</x-layouts.app>