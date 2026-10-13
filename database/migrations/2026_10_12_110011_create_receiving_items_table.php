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
        Schema::create('receiving_items', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'receiving_id', 'receivings');
            Keys::to($t, 'purchase_item_id', 'purchase_items');
            Keys::to($t, 'material_id', 'materials');
            $t->decimal('qty_received', 14, 3);
            $t->string('lot_number_input', 100)->nullable();
            $t->date('expiry_date_input')->nullable();
            $t->text('kondisi_dilaporkan')->nullable();
            $t->timestamps();
        });

        DB::statement('ALTER TABLE receiving_items ADD CONSTRAINT receiving_items_qty_chk CHECK (qty_received > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('receiving_items');
    }
};
