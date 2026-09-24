<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['Super Admin', 'admin@sultraklik.com', User::ROLE_SUPER_ADMIN],
            ['Tim Editor', 'editor@sultraklik.com', User::ROLE_EDITOR],
            ['Aqsha Reporter', 'reporter@sultraklik.com', User::ROLE_REPORTER],
        ];

        foreach ($data as [$name, $email, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'role' => $role]
            );
        }

        $this->command?->info('Akun: admin@sultraklik.com | editor@sultraklik.com | reporter@sultraklik.com — password: password');
    }
}