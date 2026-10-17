<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kepala_change_requests', function (Blueprint $t) {
            $t->id();
            Keys::sppg($t);
            Keys::user($t, 'requested_by');
            Keys::user($t, 'reviewed_by', nullable: true);
            $t->text('reason');
            $t->string('nama_baru', 150);
            $t->string('email_baru', 150);
            $t->string('no_telepon_baru', 30)->nullable();
            $t->string('foto_ktp_baru', 500)->nullable();
            $t->string('foto_surat_baru', 500)->nullable();
            $t->jsonb('old_values')->nullable();
            $t->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $t->timestamp('requested_at')->useCurrent();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_note')->nullable();
            $t->timestamps();

            $t->index(['id_sppg', 'status']);
        });

        DB::statement("CREATE UNIQUE INDEX kepala_change_requests_satu_pending ON kepala_change_requests (id_sppg) WHERE status = 'pending'");
        DB::statement('ALTER TABLE kepala_change_requests ADD CONSTRAINT kepala_change_requests_reason_chk CHECK (char_length(btrim(reason)) > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('kepala_change_requests');
    }
};
