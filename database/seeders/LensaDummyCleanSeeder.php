<?php

namespace Database\Seeders;

use App\Support\Keys;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * pake ini pas data masih dummy sblm ada data asli:
 *   php artisan db:seed --class=LensaDummyCleanSeeder
 */

class LensaDummyCleanSeeder extends Seeder
{
    /** Urut dari tabel paling "bawah" (yang merujuk) ke paling "atas", agar tidak melanggar foreign key. */
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
        });

        $this->command->info('Data Lensa MBG dikosongkan. Tabel ' . Keys::SPPG_TABLE . ' dan ' . Keys::USER_TABLE . ' tidak disentuh.');
    }
}
