<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LensaDummyCleanSeeder extends Seeder
{
    private array $tables = [
        'audit_trails',
        'correction_requests',
        'evidences',
        'waste_surplus_records',
        'distributions',
        'distribution_destinations',
        'pre_distribution_checks',
        'processing_steps',
        'material_usages',
        'production_batches',
        'stock_movements',
        'material_lots',
        'receiving_items',
        'receivings',
        'purchase_items',
        'purchases',
        'recipe_nutritions',
        'recipe_items',
        'recipes',
        'menus',
        'materials',
        'suppliers',
        'kepala_change_requests',
        'staff',
        'sppg',
        'sessions',
    ];

    public function run(): void
    {
        if ($this->command && ! $this->command->confirm('Ini akan mengosongkan semua data operasional dan akun dummy, lalu menyisakan hanya akun Admin Sistem. Lanjut?', true)) {
            $this->command->info('Dibatalkan.');

            return;
        }

        DB::transaction(function () {
            // Hapus tabel operasional dan reset sequence
            foreach ($this->tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->delete();
                    try {
                        DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), 1, false)");
                    } catch (\Throwable) {
                        // ignore jika nama kolom PK bukan 'id' atau non-sequence
                    }
                }
            }

            // Hapus semua user selain role admin
            User::where('role', '!=', 'admin')->delete();

            // Pastikan Admin Sistem tetap ada / dibuat
            if (! User::where('role', 'admin')->exists()) {
                $this->call(AdminSeeder::class);
            }
        });

        $this->command?->info('Data operasional & dummy dibersihkan. Akun Admin Sistem siap digunakan.');
    }
}
