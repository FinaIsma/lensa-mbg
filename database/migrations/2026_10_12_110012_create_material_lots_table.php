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
        Schema::create('material_lots', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'receiving_item_id', 'receiving_items', index: false); // sudah ada unique di bawah
            Keys::to($t, 'material_id', 'materials');
            Keys::to($t, 'supplier_id', 'suppliers');
            Keys::sppg($t);
            $t->string('lot_number', 100);
            $t->string('barcode', 64)->unique();
            $t->date('received_date');
            $t->date('expiry_date');
            $t->decimal('initial_qty', 14, 3);
            $t->decimal('current_qty', 14, 3);
            $t->decimal('unit_cost', 15, 2);
            $t->enum('status', ['available', 'depleted', 'voided'])->default('available');
            $t->boolean('is_locked')->default(true);
            $t->timestamps();

            $t->unique('receiving_item_id'); // 1 receiving_item = 1 lot
            $t->index(['material_id', 'status', 'expiry_date']);
        });

        DB::statement('ALTER TABLE material_lots ADD CONSTRAINT material_lots_qty_chk CHECK (initial_qty > 0 AND current_qty >= 0 AND unit_cost >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('material_lots');
    }
};
