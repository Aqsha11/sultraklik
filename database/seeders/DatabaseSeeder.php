<?php

namespace Database\Seeders;

use App\Models\BreakingNews;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            RegionSeeder::class,
            ArticleSeeder::class,
            PageSeeder::class,
        ]);

        $this->seedSettings();
        $this->seedBreakingNews();
    }

    private function seedSettings(): void
    {
        foreach ([
            'general.name' => 'SULTRAKLIK',
            'general.tagline' => 'Portal Berita Sulawesi Tenggara',
            'general.description' => 'Portal berita digital yang menyajikan informasi seputar Sulawesi Tenggara secara cepat, informatif, dan mudah diakses.',
            'general.email' => 'redaksi@sultraklik.com',
            'general.phone' => '+62 852 0000 0000',
            'general.address' => 'Kendari, Sulawesi Tenggara',
            'social.instagram' => 'https://instagram.com/sultraklik',
            'social.facebook' => 'https://facebook.com/sultraklik',
            'social.twitter' => 'https://twitter.com/sultraklik',
            'social.youtube' => 'https://youtube.com/@sultraklik',
        ] as $key => $value) {
            [$group, $k] = explode('.', $key, 2);
            Setting::updateOrCreate(['group' => $group, 'key' => $k], ['value' => $value]);
        }
    }

    private function seedBreakingNews(): void
    {
        BreakingNews::create([
            'title' => 'Pemprov Sultra siapkan program percepatan pembangunan infrastruktur jalan untuk 2027',
            'starts_at' => now()->subHours(2),
            'ends_at' => now()->addHours(10),
            'is_active' => true,
        ]);
    }
}