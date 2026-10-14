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
        Schema::create('waste_surplus_records', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'production_batch_id', 'production_batches', nullable: true);
            Keys::to($t, 'material_lot_id', 'material_lots', nullable: true);
            Keys::to($t, 'distribution_id', 'distributions', nullable: true);
            Keys::sppg($t);
            Keys::user($t, 'recorded_by');
            $t->enum('record_type', ['PRODUCTION_LOSS', 'FOOD_WASTE', 'SURPLUS', 'MATERIAL_DISPOSAL']);
            $t->decimal('qty', 14, 3);
            $t->text('reason')->nullable();
            $t->timestamp('recorded_at')->useCurrent();
            $t->enum('status', ['active', 'voided'])->default('active');
            $t->timestamps();

            $t->index(['id_sppg', 'record_type']);
        });

        DB::statement('ALTER TABLE waste_surplus_records ADD CONSTRAINT waste_surplus_qty_chk CHECK (qty > 0)');

        DB::statement("ALTER TABLE waste_surplus_records ADD CONSTRAINT waste_surplus_ref_chk CHECK (
            (record_type = 'MATERIAL_DISPOSAL' AND material_lot_id IS NOT NULL)
            OR (record_type <> 'MATERIAL_DISPOSAL' AND production_batch_id IS NOT NULL)
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('waste_surplus_records');
    }
};
