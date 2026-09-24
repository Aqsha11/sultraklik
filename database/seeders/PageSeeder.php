<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Tentang Kami',
                'slug' => 'tentang-kami',
                'content' => '<p>SULTRAKLIK adalah portal berita digital Sulawesi Tenggara yang menyajikan informasi seputar seluruh kabupaten/kota di Sultra serta peristiwa nasional.</p>',
            ],
            [
                'title' => 'Redaksi',
                'slug' => 'redaksi',
                'content' => '<p>Susunan redaksi SULTRAKLIK: Pemimpin Redaksi, Redaktur Pelaksana, dan jajaran reporter di seluruh wilayah Sulawesi Tenggara.</p>',
            ],
            [
                'title' => 'Kontak',
                'slug' => 'kontak',
                'content' => '<p>Kontak redaksi SULTRAKLIK: <br>Email: redaksi@sultraklik.com</p>',
            ],
            [
                'title' => 'Pedoman Media Siber',
                'slug' => 'pedoman-media-siber',
                'content' => '<p>SULTRAKLIK berpedoman pada Pedoman Media Siber yang dikeluarkan Dewan Pers Indonesia.</p>',
            ],
            [
                'title' => 'Kode Etik',
                'slug' => 'kode-etik',
                'content' => '<p>Setiap konten SULTRAKLIK mengikuti Kode Etik Jurnalistik.</p>',
            ],
            [
                'title' => 'Kebijakan Privasi',
                'slug' => 'kebijakan-privasi',
                'content' => '<p>Kebijakan privasi SULTRAKLIK menjelaskan bagaimana data pengunjung dikelola dan dilindungi.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                array_merge($page, ['is_active' => true, 'published_at' => now()])
            );
        }
    }
}
