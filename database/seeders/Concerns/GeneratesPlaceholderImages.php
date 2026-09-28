<?php

namespace Database\Seeders\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Membuat file gambar placeholder yang benar-benar tersimpan di disk 'public'.
 *
 * Seeder sebelumnya menulis URL eksternal (placehold.co) ke kolom gambar.
 * Nilai itu tidak bisa di-resolve oleh Filament FileUpload: preview memakai
 * Storage::exists() lalu Storage::url(), yang keduanya memperlakukan state
 * sebagai nama file di dalam disk. Akibatnya preview tidak pernah tampil dan
 * berkedip setiap kali form di-render ulang. Path relatif hasil method ini
 * aman karena benar-benar ada di disk.
 *
 * Nama file diturunkan dari judul supaya menjalankan ulang seeder memperbarui
 * file yang sama, bukan menumpuk file baru. Ukuran font dan lebar baris
 * menyesuaikan tinggi/lebar supaya tetap terbaca pada banner 970x90 maupun
 * banner vertikal 300x600.
 */
trait GeneratesPlaceholderImages
{
    /** Lebar piksel per karakter untuk imagestring font 1..5. */
    private const FONT_WIDTH = [1 => 6, 2 => 8, 3 => 9, 4 => 11, 5 => 12];

    protected function placeholderImage(
        string $title,
        ?string $label = null,
        int $width = 800,
        int $height = 450,
        string $directory = 'featured',
    ): string {
        $path = $directory.'/'.Str::limit(Str::slug($title), 50, '').'.png';

        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $path;
        }

        $disk->put($path, $this->renderPlaceholder($title, $label ?? $title, $width, $height));

        return $path;
    }

    private function renderPlaceholder(string $title, string $label, int $width, int $height): string
    {
        $hash = crc32($title);
        $r = ($hash >> 16) & 0xFF;
        $g = ($hash >> 8) & 0xFF;
        $b = $hash & 0xFF;

        $image = imagecreatetruecolor($width, $height);
        imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, $r, $g, $b));

        $bandTop = (int) round($height * 0.60);
        imagefilledrectangle(
            $image,
            0,
            $bandTop,
            $width,
            $height,
            imagecolorallocatealpha($image, (int) ($r * 0.55), (int) ($g * 0.55), (int) ($b * 0.55), 40),
        );

        $white = imagecolorallocate($image, 255, 255, 255);
        $muted = imagecolorallocatealpha($image, 255, 255, 255, 60);

        $font = $height >= 300 ? 5 : ($height >= 150 ? 4 : 2);
        $brandFont = $height >= 150 ? 3 : 1;
        $padding = max(12, (int) round($width * 0.05));

        if ($height >= 150) {
            imagestring($image, $brandFont, $padding, $padding, 'SULTRAKLIK', $white);
            imagestring($image, 1, $padding, $padding + 14, 'Placeholder - gambar demo', $muted);
        }

        $chars = max(8, intdiv($width - (2 * $padding), self::FONT_WIDTH[$font]));
        $lineHeight = self::FONT_WIDTH[$font] + 4;
        $y = max($padding, $bandTop + 14);

        foreach ($this->wrap($label, $chars) as $line) {
            if ($y + $lineHeight > $height) {
                break;
            }

            imagestring($image, $font, $padding, $y, $line, $white);
            $y += $lineHeight;
        }

        ob_start();
        imagepng($image, null, 6);
        imagedestroy($image);

        return (string) ob_get_clean();
    }

    /** @return list<string> */
    private function wrap(string $text, int $length): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            if ($line !== '' && strlen($line) + 1 + strlen($word) > $length) {
                $lines[] = $line;
                $line = $word;

                continue;
            }

            $line = $line === '' ? $word : $line.' '.$word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }
}
