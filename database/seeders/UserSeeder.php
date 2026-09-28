<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Membuat satu-satunya akun super admin.
 *
 * Keamanan:
 * - Tidak ada password default. Dulu seeder ini membuat tiga akun demo
 *   (admin/editor/reporter) semuanya dengan password 'password', yang berarti
 *   ada akun dengan password sama sekali bukan rahasia begitu situs tayang.
 * - Password dibaca dari env SUPER_ADMIN_PASSWORD. Kalau kosong di production
 *   seeder berhenti, bukan membuat password lemah.
 * - Kalau akun sudah ada, password TIDAK disentuh, supaya `db:seed` ulang
 *   tidak menimpa password yang sudah diganti pemilik akun.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@sultraklik.id');

        if (User::where('email', $email)->exists()) {
            $this->command?->info("Super admin {$email} sudah ada, password tidak diubah.");

            return;
        }

        $password = $this->resolvePassword();

        User::create([
            'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        $this->command?->info("Super admin dibuat: {$email}");

        if (app()->environment('production')) {
            $this->command?->warn('Ganti password ini setelah login pertama, lalu hapus SUPER_ADMIN_PASSWORD dari .env.');
        } else {
            $this->command?->info("Password: {$password}");
        }
    }

    private function resolvePassword(): string
    {
        $password = (string) env('SUPER_ADMIN_PASSWORD', '');

        if ($password === '') {
            if (app()->environment('production')) {
                throw new RuntimeException(
                    'SUPER_ADMIN_PASSWORD belum diisi. Isi di .env server dengan password '
                    .'minimal 12 karakter sebelum menjalankan db:seed. Menolak membuat akun '
                    .'super admin tanpa password.'
                );
            }

            // Lokal/uji: buat acak agar tidak ada kredensial yang bisa ditebak,
            // lalu dicetak sekali di console supaya bisa dipakai.
            return Str::password(24);
        }

        if (strlen($password) < 12) {
            throw new RuntimeException(
                'SUPER_ADMIN_PASSWORD minimal 12 karakter, sekarang '.strlen($password).'.'
            );
        }

        return $password;
    }
}
