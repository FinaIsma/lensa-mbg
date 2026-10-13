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
        Schema::create('material_usages', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'production_batch_id', 'production_batches');
            Keys::to($t, 'material_lot_id', 'material_lots');
            $t->decimal('qty_used', 14, 3);
            $t->decimal('unit_cost_snapshot', 15, 2)->nullable();
            $t->timestamps();

            $t->unique(['production_batch_id', 'material_lot_id']);
        });

        DB::statement('ALTER TABLE material_usages ADD CONSTRAINT material_usages_qty_chk CHECK (qty_used > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('material_usages');
    }
};
