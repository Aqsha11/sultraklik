<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Category;
use App\Models\Region;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleBulkSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', User::ROLE_EDITOR)->first() ?? User::first();
        $categories = Category::where('is_active', true)->where('slug', '!=', 'sultra')->get();
        $regions = Region::where('is_active', true)->get();

        if ($categories->isEmpty() || $regions->isEmpty()) {
            $this->command?->error('Seeder butuh data kategori dan region. Jalankan db:seed dahulu.');

            return;
        }

        $verbs = ['Dorong', 'Percepat', 'Kaji', 'Pastikan', 'Kawal', 'Optimalkan', 'Perkuat', 'Siapkan', 'Luncurkan', 'Tuntaskan'];
        $subjects = ['Pembangunan', 'Program', 'Proyek', 'Kebijakan', 'Anggaran', 'Inovasi', 'Layanan', 'Rencana', 'Perbaikan', 'Revitalisasi'];
        $objects = [
            'Infrastruktur Jalan', 'Pelayanan Publik', 'Sektor Pariwisata', 'UMKM Lokal', 'Sektor Pertanian',
            'Pendidikan Vokasi', 'Layanan Kesehatan', 'Bantuan Sosial', 'Sistem Digitalisasi', 'Jaringan Listrik',
            'Pasar Rakyat', 'Sarana Olahraga', 'Kawasan Pantai', 'Hutan Lindung', 'Jembatan Penghubung',
        ];
        $events = [
            'Peristiwa warga', 'Kegiatan sosial', 'Musyawarah desa', 'Fenomena alam', 'Insiden lalu lintas',
            'Operasi pasar', 'Kunjungan kerja', 'Pelantikan pejabat', 'Gelaran budaya', 'Lomba tingkat daerah',
        ];

        $created = 0;

        for ($i = 0; $i < 40; $i++) {
            $category = $categories[$i % $categories->count()];
            $region = $regions[($i + 3) % $regions->count()];

            $style = $i % 3;
            if ($style === 0) {
                $verb = $verbs[$i % count($verbs)];
                $subject = $subjects[($i + 1) % count($subjects)];
                $title = trim($verb.' '.$subject.' '.($region->name ?? '').' - Warga Sultra Menyambut');
            } elseif ($style === 1) {
                $event = $events[$i % count($events)];
                $title = ucfirst($event).' di '.($region->name ?? 'Kendari').' Menjadi Sorotan Publik';
            } else {
                $title = 'Cerita Sukses '.($region->name ?? 'Daerah').': Transformasi '.$subjects[$i % count($subjects)].' Mengubah Wajah Daerah';
            }
            $title = Str::limit($title, 120, '');

            $views = random_int(50, 2500);
            $isPicked = ($i % 10) === 0;
            $publishedAt = now()->subHours(random_int(1, 400));
            if ($isPicked) {
                $publishedAt = now()->subDays(random_int(5, 25));
            }

            $slug = Str::slug($title);

            $palette = ['374151', '1F2937', '3B82F6', 'DC2626', '059669', 'B45309', '7C3AED', '0EA5E9'];

            $article = Article::updateOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'excerpt' => Str::limit($title, 110),
                    'featured_image' => 'https://placehold.co/800x450/'.$palette[$i % count($palette)].'/ffffff?text='.Str::slug($title),
                    'content' => implode("\n", [
                        '<p><strong>'.$title.'</strong></p>',
                        '<p>SULTRAKLIK, '.($region->name ?: 'Kendari').' — Warga menyambut positif perkembangan ini. Pemerintah daerah terus berkoordinasi dengan seluruh pihak demi mewujudkan kepentingan masyarakat Sulawesi Tenggara.</p>',
                        '<h2>Latar Belakang</h2><p>Sebelumnya, sejumlah pihak mendorong percepatan penanganan isu ini agar berdampak langsung bagi warga. Dukungan lintas sektor diharapkan memperkuat hasil di lapangan.</p>',
                        '<blockquote><p>"Kami berkomitmen menjaga transparansi dan keberlanjutan setiap program," ujar perwakilan daerah setempat.</p></blockquote>',
                        '<p>Redaksi SULTRAKLIK akan terus memantau perkembangan dan menyajikan informasi terbaru seputar Sulawesi Tenggara.</p>',
                    ]),
                    'category_id' => $category->id,
                    'region_id' => $region->id,
                    'author_id' => $user->id,
                    'status' => Article::STATUS_PUBLISHED,
                    'published_at' => $publishedAt,
                    'is_headline' => false,
                    'is_featured' => $isPicked,
                    'views' => $views,
                    'comments_count' => random_int(0, 30),
                    'likes_count' => random_int(0, 500),
                ]
            );

            $tag = Tag::updateOrCreate(
                ['slug' => Str::slug($category->name)],
                ['name' => $category->name]
            );
            $article->tags()->syncWithoutDetaching([$tag->id]);

            $created++;
        }

        $this->command?->info("Artikel bulk ditambahkan: {$created} berita.");
    }
}
