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
        Schema::create('recipe_items', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'recipe_id', 'recipes');
            Keys::to($t, 'material_id', 'materials');
            $t->decimal('qty_per_porsi', 14, 4);
            $t->string('satuan', 20); // disalin dari materials.satuan
            $t->timestamps();

            $t->unique(['recipe_id', 'material_id']);
        });

        DB::statement('ALTER TABLE recipe_items ADD CONSTRAINT recipe_items_qty_chk CHECK (qty_per_porsi > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('recipe_items');
    }
};
