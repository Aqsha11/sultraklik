<x-layouts.app title="Pencarian">
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="font-serif-news font-black text-3xl mb-6">HASIL PENCARIAN</h1>

        <form method="GET" action="{{ url('/search') }}" class="flex gap-2 mb-6">
            <input type="text" name="q" value="{{ $query }}" placeholder="Ketik kata kunci..."
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-red-500">
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg font-semibold text-sm">Cari</button>
        </form>

        @if($query !== '')
            <p class="text-sm text-gray-600 mb-4">Ditemukan <span class="font-bold">{{ $articles->total() }}</span> berita untuk "<span class="font-bold">{{ $query }}</span>"</p>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($articles as $article)
                <x-news-card :article="$article" textSize="text-lg" excerpt="true" />
            @empty
                <div class="bg-white md:col-span-2 p-10 text-center text-gray-500 rounded-lg border border-gray-200">
                    Tidak ada berita yang ditemukan.
                </div>
            @endforelse
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    </div>
</x-layouts.app>