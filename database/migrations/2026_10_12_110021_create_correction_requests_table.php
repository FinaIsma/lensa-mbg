<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// kolom correction_requests.target_table isinya nama tabel yang dikoreksi
// nama modul buat tampilan dan filter dibuat di kode lewat pemetaan tabel ke modul, ga disimpen di database.
// pengategorian tabel jd modul ini nanti taro di model aja atau trsrh
// receiving_items, purchase_items, production_batches, material_usages, distributions, waste_surplus_records, material_lots


return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correction_requests', function (Blueprint $t) {
            $t->id();
            Keys::sppg($t);
            Keys::user($t, 'requested_by');
            Keys::user($t, 'reviewed_by', nullable: true);
            $t->string('request_no', 40)->unique();
            $t->string('target_table', 100);
            $t->unsignedBigInteger('target_id');
            $t->string('field_name', 100);
            $t->text('old_value')->nullable();
            $t->text('new_value')->nullable();
            $t->text('reason');
            $t->enum('status', ['pending', 'approved', 'rejected', 'applied', 'cancelled'])->default('pending');
            $t->timestamp('requested_at')->useCurrent();
            $t->timestamp('reviewed_at')->nullable();
            $t->text('review_note')->nullable();
            $t->timestamp('applied_at')->nullable();
            $t->timestamps();

            $t->index(['target_table', 'target_id']);
            $t->index(['id_sppg', 'status']);
        });

        DB::statement('ALTER TABLE correction_requests ADD CONSTRAINT correction_requests_reason_chk CHECK (char_length(btrim(reason)) > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
