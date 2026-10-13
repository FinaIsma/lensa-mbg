<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Modul 12. Hasil Produksi  |  tabel: pre_distribution_checks
 * Hasil produksi: portions_ready menjadi production_batches.actual_porsi (diisi oleh kode, bukan trigger).
 * reported_condition = kondisi menurut laporan admin (teks), bukan hasil uji.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pre_distribution_checks', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'production_batch_id', 'production_batches');
            Keys::user($t, 'checked_by');
            $t->unsignedInteger('portions_ready');
            $t->text('reported_condition')->nullable();
            $t->text('note')->nullable();
            $t->timestamp('checked_at')->useCurrent();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_distribution_checks');
    }
};
