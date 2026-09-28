<?php

namespace Tests\Fixtures;

use App\Models\Article;
use App\Models\BreakingNews;
use App\Models\Category;
use App\Models\Page;
use App\Models\Region;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\Concerns\GeneratesPlaceholderImages;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Konten demo KHUSUS TEST.
 *
 * Ini pernah tinggal di database/seeders, tapi dipindah ke sini saat data demo
 * dihapus dari produksi: situs yang baru di-deploy tidak boleh berisi artikel,
 * kategori, atau halaman contoh. Test tetap butuh isi supaya halaman publik bisa
 * dirender, jadi datanya hidup di tests/ dan tidak pernah ikut `db:seed`.
 *
 * Isinya sengaja dibuat kecil: cukup untuk menutup setiap assert test publik,
 * bukan tiruan situs sebenarnya.
 */
class DemoContentSeeder extends Seeder
{
    use GeneratesPlaceholderImages;

    public function run(): void
    {
        $this->seedSettings();
        $this->seedCategories();
        $this->seedRegions();
        $this->seedPages();
        $this->seedArticles();
        $this->seedBreakingNews();
    }

    private function seedSettings(): void
    {
        foreach ([
            'general.name' => 'SULTRAKLIK',
            'general.tagline' => 'Portal Berita Sulawesi Tenggara',
            'general.description' => 'Portal berita digital Sulawesi Tenggara.',
            'seo.meta_title' => 'SULTRAKLIK — Portal Berita Sulawesi Tenggara',
            'seo.meta_description' => 'Deskripsi bawaan situs untuk pengujian meta tag.',
            'seo.meta_keywords' => 'berita sultra, sulawesi tenggara',
        ] as $key => $value) {
            [$group, $k] = explode('.', $key, 2);
            Setting::updateOrCreate(['group' => $group, 'key' => $k], ['value' => $value]);
        }
    }

    private function seedCategories(): void
    {
        // 'sultra' bukan kategori biasa: rute /sultra dan artikelnya punya
        // perlakuan khusus, jadi harus selalu ada di fixture ini.
        foreach ([
            ['Sultra', '#d11a2a', 1],
            ['Politik', '#7c3aed', 2],
            ['Peristiwa', '#ea580c', 3],
            ['Ekonomi', '#15803d', 4],
            ['Olahraga', '#1d4ed8', 5],
            ['Lifestyle', '#c026d3', 6],
        ] as [$name, $color, $order]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'color' => $color, 'order_column' => $order, 'is_active' => true],
            );
        }
    }

    private function seedRegions(): void
    {
        foreach (['Kendari', 'Konawe', 'Konawe Utara', 'Baubau', 'Kolaka'] as $i => $name) {
            Region::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'order_column' => $i + 1, 'is_active' => true],
            );
        }
    }

    private function seedPages(): void
    {
        // PublicPagesTest memakai '/page/tentang-kami' secara literal.
        foreach ([
            ['Tentang Kami', 'tentang-kami', '<p>Portal berita Sulawesi Tenggara.</p>'],
            ['Redaksi', 'redaksi', '<p>Susunan redaksi.</p>'],
            ['Kontak', 'kontak', '<p>Email: redaksi@sultraklik.com</p>'],
        ] as [$title, $slug, $content]) {
            Page::updateOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => $content, 'is_active' => true, 'published_at' => now()],
            );
        }
    }

    private function seedArticles(): void
    {
        $user = User::first() ?? User::create([
            'name' => 'Redaksi Uji',
            'email' => 'redaksi@contoh.test',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->article(
            $user, '27 Siswa SMA Negeri Konawe Diduga Keracunan Usai Santap MBG di Sekolah',
            'peristiwa', 'konawe', ['Konawe', 'MBG'], 1250, now()->subHours(2), headline: true,
        );

        $this->article(
            $user, 'Ketua DPRD Kendari Dorong Percepatan Revitalisasi Pasar Rakyat',
            'politik', 'kendari', ['Politik', 'Kendari'], 1020, now()->subDay(),
        );

        $this->article(
            $user, 'Harga Bahan Pokok di Baubau Naik Tipis Jelang Akhir Tahun',
            'ekonomi', 'baubau', ['Ekonomi', 'Baubau'], 780, now()->subDays(2),
        );

        $this->article(
            $user, 'Persba Kendari Menang Telak atas Tim Tamu di Kolaka',
            'olahraga', 'konawe', ['Olahraga'], 640, now()->subDays(3),
        );

        // Kategori sultra: menguji jalur highlight SULTRA saat artikel dibuka.
        $this->article(
            $user, 'Pemprov Sultra Rampungkan Pembangunan Jalan Poros Kendari Konawe Utara',
            'sultra', 'konawe-utara', ['Infrastruktur', 'Sultra'], 890, now()->subHours(5),
        );

        // Artikel video, dipakai test_homepage_shows_video_section.
        $this->article(
            $user, 'Video Dentuman Petir Disertai Hujan Ringan di Konawe',
            'peristiwa', 'konawe', ['Video', 'Konawe'], 2410, now()->subHours(3),
            videoUrl: 'https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=30s',
        );

        // Artikel tanpa video, dipakai test_article_without_video_has_no_player.
        $this->article(
            $user, 'Gaya Hidup Sehat di Era Modern',
            'lifestyle', 'kendari', ['Lifestyle'], 520, now()->subDays(4),
        );
    }

    private function seedBreakingNews(): void
    {
        // Dua item supaya cabang marquee (count > 1) ikut ter-cover.
        foreach ([
            'Pemprov Sultra siapkan program percepatan pembangunan infrastruktur',
            'Harga BBM bersubsidi untuk Sulawesi Tenggara tidak berubah',
        ] as $title) {
            BreakingNews::updateOrCreate(
                ['title' => $title],
                ['title' => $title, 'is_active' => true, 'starts_at' => now()->subHour(), 'ends_at' => null],
            );
        }
    }

    private function article(
        User $user,
        string $title,
        string $categorySlug,
        string $regionSlug,
        array $tags,
        int $views,
        Carbon $publishedAt,
        bool $headline = false,
        ?string $videoUrl = null,
    ): void {
        $category = Category::where('slug', $categorySlug)->first();
        $region = Region::where('slug', $regionSlug)->first();

        if (! $category) {
            return;
        }

        $excerpt = Str::limit($title, 100);

        $article = Article::updateOrCreate(
            ['slug' => Str::slug($title)],
            [
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => '<p><strong>'.$title.'</strong></p><p>Isi artikel contoh untuk keperluan test.</p>',
                'featured_image' => $this->placeholderImage($title),
                'video_url' => $videoUrl,
                'category_id' => $category->id,
                'region_id' => $region?->id,
                'author_id' => $user->id,
                'status' => Article::STATUS_PUBLISHED,
                'published_at' => $publishedAt,
                // Article::booted() mewajibkan XOR: kalau headline menyala,
                // featured harus mati.
                'is_headline' => $headline,
                'is_featured' => ! $headline,
                'views' => $views,
                'comments_count' => 0,
                'likes_count' => 0,
                'seo_title' => $title,
                'seo_description' => $excerpt,
            ],
        );

        $tagModels = collect($tags)->map(fn (string $tag) => Tag::updateOrCreate(
            ['slug' => Str::slug($tag)],
            ['name' => $tag],
        ));

        $article->tags()->sync($tagModels->pluck('id'));
    }
}
