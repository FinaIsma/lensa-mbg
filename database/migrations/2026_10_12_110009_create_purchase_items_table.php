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
        Schema::create('purchase_items', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'purchase_id', 'purchases');
            Keys::to($t, 'material_id', 'materials');
            $t->decimal('qty_ordered', 14, 3);
            $t->decimal('unit_price', 15, 2);
            $t->timestamps();
        });

        DB::statement('ALTER TABLE purchase_items ADD CONSTRAINT purchase_items_qty_chk CHECK (qty_ordered > 0 AND unit_price >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
