<?php

namespace Database\Seeders;

use App\Support\Keys;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class LensaDummyCleanSeeder extends Seeder
{
    private array $tables = [
        'audit_trails', 'correction_requests', 'evidences', 'waste_surplus_records', 'distributions',
        'pre_distribution_checks', 'processing_steps', 'material_usages', 'production_batches',
        'stock_movements', 'material_lots', 'receiving_items', 'receivings', 'purchase_items', 'purchases',
        'recipe_items', 'recipes', 'distribution_destinations', 'menus', 'materials', 'suppliers',
    ];

    public function run(): void
    {
        if (! $this->command->confirm('Ini menghapus SEMUA data tabel Lensa MBG (termasuk yang bukan dummy). Lanjut?', false)) {
            $this->command->info('Dibatalkan.');
            return;
        }

        DB::transaction(function () {
            foreach ($this->tables as $table) {
                DB::table($table)->delete();
                DB::statement("SELECT setval(pg_get_serial_sequence('{$table}', 'id'), 1, false)");
            }

            if (Schema::hasTable('staff')) {
                DB::statement("DELETE FROM staff WHERE email LIKE '%@lensa.test'");
            }
        });

        $this->command->info('Data Lensa MBG dikosongkan. Tabel ' . Keys::SPPG_TABLE . ', ' . Keys::USER_TABLE . ' dan staff asli tidak disentuh.');
    }
}
