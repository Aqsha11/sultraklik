<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Database\Seeders\Concerns\GeneratesPlaceholderImages;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    use GeneratesPlaceholderImages;

    public function run(): void
    {
        $ads = [
            ['Banner Header 970x90', 'homepage_top', 970, 90],
            ['Banner Sidebar Vertikal 300x600', 'sidebar', 300, 600],
            ['Banner Sidebar Vertikal 300x600 - 2', 'sidebar', 300, 600],
        ];

        Advertisement::whereNotIn('position', ['homepage_top', 'sidebar'])->delete();

        foreach ($ads as [$title, $position, $width, $height]) {
            Advertisement::updateOrCreate(
                ['position' => $position, 'title' => $title],
                [
                    'type' => 'image',
                    'image' => $this->placeholderImage($title, 'IKLAN', $width, $height, 'ads'),
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Iklan demo ditambahkan: '.count($ads).' slot.');
    }
}
