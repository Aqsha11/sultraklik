<?php

namespace Database\Seeders;

use App\Models\Advertisement;
use Illuminate\Database\Seeder;

class AdvertisementSeeder extends Seeder
{
    public function run(): void
    {
        $ads = [
            ['Banner Header 970x90', 'homepage_top', 'https://placehold.co/970x90/DC2626/ffffff?text=SULTRAKLIK+%7C+Header'],
            ['Banner Sidebar Vertikal 300x600', 'sidebar', 'https://placehold.co/300x600/F59E0B/ffffff?text=IKLAN+SIDEBAR'],
            ['Banner Sidebar Vertikal 300x600 - 2', 'sidebar', 'https://placehold.co/300x600/0EA5E9/ffffff?text=IKLAN+SIDEBAR+2'],
        ];

        Advertisement::whereNotIn('position', ['homepage_top', 'sidebar'])->delete();

        foreach ($ads as [$title, $position, $image]) {
            Advertisement::updateOrCreate(
                ['position' => $position, 'title' => $title],
                ['type' => 'image', 'image' => $image, 'is_active' => true]
            );
        }

        $this->command?->info('Iklan demo ditambahkan: '.count($ads).' slot.');
    }
}
