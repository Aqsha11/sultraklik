<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seeder produksi.
 *
 * Isi sengaja sangat minimal: satu akun super admin, slot iklan, dan baris
 * settings dasar. Tidak ada artikel, kategori, wilayah, atau halaman contoh —
 * data demo tidak boleh ikut ke server, dan memanggil seeder artikel di
 * produksi berarti menimpa konten yang sudah diisi redaksi.
 *
 * Test tidak memakai seeder ini; mereka memakai Tests\Fixtures\DemoContentSeeder.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AdvertisementSeeder::class,
        ]);

        $this->seedSettings();
    }

    /**
     * Settings dasar wajib ada: layout memanggil Setting::get() sekitar 20 kali
     * per render untuk nama situs, tagline, dan SEO. Tanpa baris ini situs yang
     * baru di-deploy tampil dengan teks kosong dan warna brand jatuh ke fallback.
     *
     * Setiap kunci bertema punya consumer di tiga tempat sekaligus —
     * resources/views/components/layouts/app.blade.php, public/css/night.css, dan
     * AdminPanelProvider. Menambah kunci berarti menyentuh ketiganya.
     */
    private function seedSettings(): void
    {
        foreach ([
            'general.name' => 'SULTRAKLIK',
            'general.tagline' => 'Portal Berita Sulawesi Tenggara',
            'general.description' => 'Portal berita digital yang menyajikan informasi seputar Sulawesi Tenggara secara cepat, informatif, dan mudah diakses.',
            'general.email' => 'redaksi@sultraklik.id',
            'general.phone' => '+62 852 0000 0000',
            'general.address' => 'Kendari, Sulawesi Tenggara',
            'social.instagram' => 'https://instagram.com/sultraklik',
            'social.facebook' => 'https://facebook.com/sultraklik',
            'social.twitter' => 'https://twitter.com/sultraklik',
            'social.youtube' => 'https://youtube.com/@sultraklik',
            'seo.meta_title' => 'SULTRAKLIK — Portal Berita Sulawesi Tenggara',
            'seo.meta_description' => 'Berita terbaru dan paling terbarukan dari Sulawesi Tenggara: politik, pemerintahan, ekonomi, pendidikan, kesehatan, olahraga, dan peristiwa. Segera baca berita pilihan redaksi SULTRAKLIK.',
            'seo.meta_keywords' => 'berita sultra, sulawesi tenggara, berita kendari, kabar terbaru, portal berita sultra',
        ] as $key => $value) {
            [$group, $k] = explode('.', $key, 2);
            Setting::updateOrCreate(['group' => $group, 'key' => $k], ['value' => $value]);
        }
    }
}
