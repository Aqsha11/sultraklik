<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Headline;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', User::ROLE_EDITOR)->first() ?? User::first();

        $this->seedOne(
            '27 Siswa SMA Negeri Konawe Diduga Keracunan Usai Santap MBG di Sekolah',
            $user,
            'peristiwa',
            'konawe',
            ['Konawe', 'MBG', 'Pendidikan', 'Peristiwa'],
            1250,
            now()->subHours(2),
            true
        );

        $this->seedOne(
            'Pemprov Sultra Rampungkan Pembangunan Jalan Poros Kendari–Konawe Utara',
            $user,
            'pemerintahan',
            'konawe-utara',
            ['Infrastruktur', 'Pemerintahan', 'Kendari'],
            890,
            now()->subHours(5),
            false
        );

        $this->seedOne(
            'Ketua DPRD Kendari Dorong Percepatan Revitalisasi Pasar Rakyat',
            $user,
            'politik',
            'kendari',
            ['Politik', 'Kendari', 'DPRD'],
            1020,
            now()->subDay(),
            false
        );

        $this->seedOne(
            'Polres Baubau Tangkap Dua Pelaku Pencurian Sepeda Motor',
            $user,
            'hukum',
            'baubau',
            ['Hukum', 'Baubau', 'Kriminal'],
            1320,
            now()->subDay(),
            false
        );

        $this->seedOne(
            'Panen Raya Jagung di Bombana Capai 5.000 Ton, Petani Optimis',
            $user,
            'ekonomi',
            'bombana',
            ['Ekonomi', 'Bombana', 'Pertanian'],
            640,
            now()->subDay()->subHours(4),
            false
        );

        $this->seedOne(
            'Universitas Halu Oleo Buka Pendaftaran Jalur Mandiri 2026',
            $user,
            'pendidikan',
            'kendari',
            ['Pendidikan', 'Kendari', 'UHO'],
            740,
            now()->subDay()->subHours(8),
            false
        );

        $this->seedOne(
            'RS Bahteramas Kendari Siapkan Layanan IGD 24 Jam Selama Libur Akhir Tahun',
            $user,
            'kesehatan',
            'kendari',
            ['Kesehatan', 'Kendari'],
            480,
            now()->subDays(2),
            false
        );
    }

    private function seedOne(
        string $title,
        User $user,
        string $categorySlug,
        string $regionSlug,
        array $tags,
        int $views,
        Carbon $publishedAt,
        bool $isFeatured
    ): void {
        $category = Category::where('slug', $categorySlug)->first();
        $region = Region::where('slug', $regionSlug)->first();
        if (! $category) {
            return;
        }

        $excerpt = Str::limit($title, 100);
        $content = [
            '<p><strong>'.$title.'</strong></p>',
            '<p>SULTRAKLIK, '.($region?->name ?: 'Kendari').' — Berikut laporan lengkap dari lapangan seputar '.$title.'. Informasi ini disusun redaksi SULTRAKLIK secara cepat dan akurat untuk pembaca di Sulawesi Tenggara.</p>',
            '<h2>Kronologi</h2><p>Peristiwa ini menjadi perhatian warga setempat. Pihak terkait terus berkoordinasi untuk menangani situasi dan memberikan keterangan resmi kepada publik.</p>',
            '<blockquote><p>"Kami berharap masyarakat tetap tenang dan mempercayakan penanganan kepada pihak yang berwenang," ujar salah satu pejabat terkait saat ditemui.</p></blockquote>',
            '<p>Redaksi SULTRAKLIK akan terus mengikuti perkembangan dan memperbarui informasi penting lainnya seputar Sulawesi Tenggara. Kami berkomitmen menyajikan berita yang akurat, berimbang, dan terpercaya untuk seluruh pembaca.</p>',
        ];

        $article = Article::updateOrCreate(
            ['slug' => Str::slug($title)],
            array_merge([
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => implode("\n", $content),
                'category_id' => $category->id,
                'region_id' => $region?->id,
                'author_id' => $user->id,
                'status' => Article::STATUS_PUBLISHED,
                'published_at' => $publishedAt,
                'is_headline' => false,
                'is_featured' => $isFeatured,
                'views' => $views,
                'comments_count' => random_int(0, 20),
                'seo_title' => $title,
                'seo_description' => $excerpt,
            ])
        );

        $tagModels = collect($tags)->map(fn (string $tag) => Tag::updateOrCreate(
            ['slug' => Str::slug($tag)],
            ['name' => $tag]
        ));
        $article->tags()->sync($tagModels->pluck('id'));

        if ($isFeatured) {
            Headline::updateOrCreate(
                ['position' => 1, 'is_active' => true],
                ['article_id' => $article->id, 'is_active' => true]
            );
            $article->update(['is_headline' => true]);
        }
    }
}