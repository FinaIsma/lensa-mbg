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
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'material_lot_id', 'material_lots');
            Keys::user($t, 'created_by');
            $t->enum('movement_type', [
                'RECEIVE_IN', 'PRODUCTION_OUT', 'DISPOSAL_OUT',
                'CORRECTION_IN', 'CORRECTION_OUT', 'VOID_REVERSAL',
            ]);
            $t->enum('direction', ['IN', 'OUT']);
            $t->decimal('qty', 14, 3);
            $t->string('reference_type', 100)->nullable();
            $t->unsignedBigInteger('reference_id')->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['reference_type', 'reference_id']);
        });

        DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_qty_chk CHECK (qty > 0)');

        DB::statement("ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_direction_chk CHECK (
            (movement_type IN ('RECEIVE_IN', 'CORRECTION_IN') AND direction = 'IN')
            OR (movement_type IN ('PRODUCTION_OUT', 'DISPOSAL_OUT', 'CORRECTION_OUT') AND direction = 'OUT')
            OR movement_type = 'VOID_REVERSAL'
        )");
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
