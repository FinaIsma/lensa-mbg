<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Seed the default System Administrator account.
     */
    public function run(): void
    {
        $email = env('ADMIN_DEFAULT_EMAIL', 'admin@lensambg.com');
        $password = env('ADMIN_DEFAULT_PASSWORD', 'password123');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'nama' => 'Admin Sistem',
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );

        $this->command?->info("Akun Admin Sistem berhasil disiapkan: {$admin->email} ({$admin->nama})");
    }
}
