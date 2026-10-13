<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Modul 8. Receiving  |  tabel: receivings
 * Dokumen penerimaan bahan. receiving_no dibuat sistem (contoh RCV-20261011-001), unik.
 * received_at = waktu barang fisik diterima (diisi user); confirmed_at = waktu konfirmasi di sistem (server).
 * Setelah confirmed: is_locked = true, perubahan hanya lewat Correction Request.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receivings', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'purchase_id', 'purchases');
            Keys::sppg($t);
            Keys::user($t, 'received_by');
            $t->string('receiving_no', 40)->unique();
            $t->timestamp('received_at');
            $t->enum('status', ['draft', 'confirmed', 'voided'])->default('draft');
            $t->boolean('is_locked')->default(false);
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receivings');
    }
};
