<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['Sultra', '#d11a2a', 1],
            ['Nasional', '#2563eb', 2],
            ['Politik', '#7c3aed', 3],
            ['Pemerintahan', '#0891b2', 4],
            ['Hukum', '#4d7c0f', 5],
            ['Peristiwa', '#ea580c', 6],
            ['Ekonomi', '#15803d', 7],
            ['Pendidikan', '#b45309', 8],
            ['Kesehatan', '#be123c', 9],
            ['Olahraga', '#1d4ed8', 10],
            ['Lifestyle', '#c026d3', 11],
            ['Teknologi', '#0f766e', 12],
        ];

        foreach ($categories as [$name, $color, $order]) {
            Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'color' => $color, 'order_column' => $order, 'is_active' => true]
            );
        }
    }
}