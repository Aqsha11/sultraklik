<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RegionSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            'Kendari', 'Baubau', 'Konawe', 'Konawe Utara', 'Konawe Selatan',
            'Kolaka', 'Kolaka Utara', 'Kolaka Timur', 'Bombana',
            'Muna', 'Muna Barat', 'Buton', 'Buton Selatan', 'Buton Tengah', 'Buton Utara', 'Wakatobi',
        ];

        foreach ($regions as $i => $name) {
            Region::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'order_column' => $i + 1, 'is_active' => true]
            );
        }
    }
}