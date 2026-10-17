<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $kolom = [
        ['purchases', 'created_by'],
        ['receivings', 'received_by'],
        ['stock_movements', 'created_by'],
        ['production_batches', 'created_by'],
        ['processing_steps', 'recorded_by'],
        ['pre_distribution_checks', 'checked_by'],
        ['distributions', 'recorded_by'],
        ['waste_surplus_records', 'recorded_by'],
        ['evidences', 'uploaded_by'],
        ['correction_requests', 'requested_by'],
    ];

    private function pastikanKosong(): void
    {
        foreach (array_unique(array_column($this->kolom, 0)) as $tabel) {
            if (DB::table($tabel)->exists()) {
                throw new RuntimeException(
                    "Tabel '{$tabel}' masih berisi data, sehingga tujuan foreign key pelaku tidak bisa dipindah ke staff. "
                    . 'Kosongkan dulu: php artisan db:seed --class=LensaDummyCleanSeeder (lalu jalankan migrate lagi).'
                );
            }
        }
    }

    private function arahkan(string $tabelTujuan, string $pkTujuan): void
    {
        foreach ($this->kolom as [$tabel, $kolom]) {
            DB::statement("ALTER TABLE \"{$tabel}\" DROP CONSTRAINT IF EXISTS \"{$tabel}_{$kolom}_foreign\"");
            DB::statement(
                "ALTER TABLE \"{$tabel}\" ADD CONSTRAINT \"{$tabel}_{$kolom}_foreign\" "
                . "FOREIGN KEY (\"{$kolom}\") REFERENCES \"{$tabelTujuan}\" (\"{$pkTujuan}\") ON DELETE RESTRICT"
            );
        }
    }

    public function up(): void
    {
        $this->pastikanKosong();
        $this->arahkan('staff', 'id');
    }

    public function down(): void
    {
        $this->pastikanKosong();
        $this->arahkan(Keys::USER_TABLE, Keys::USER_PK);
    }
};
