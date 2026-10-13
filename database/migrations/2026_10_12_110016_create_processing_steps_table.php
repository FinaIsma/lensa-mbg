<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Modul 12. Pengolahan  |  tabel: processing_steps
 * Tahap pengolahan (Persiapan bahan, Pencucian, Pemasakan, Pengemasan, ...).
 * Tidak ada step_order: urutan tampil memakai started_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processing_steps', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'production_batch_id', 'production_batches');
            Keys::user($t, 'recorded_by');
            $t->string('step_name', 150);
            $t->timestamp('started_at');
            $t->timestamp('ended_at')->nullable();
            $t->enum('status', ['in_progress', 'done'])->default('in_progress');
            $t->text('note')->nullable();
            $t->timestamps();
        });

        DB::statement('ALTER TABLE processing_steps ADD CONSTRAINT processing_steps_time_chk CHECK (ended_at IS NULL OR ended_at >= started_at)');
    }

    public function down(): void
    {
        Schema::dropIfExists('processing_steps');
    }
};
