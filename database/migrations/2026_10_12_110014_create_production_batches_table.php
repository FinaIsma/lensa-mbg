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
        Schema::create('production_batches', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'menu_id', 'menus');
            Keys::to($t, 'recipe_id', 'recipes');
            Keys::sppg($t);
            Keys::user($t, 'created_by');
            $t->string('batch_code', 40)->unique();
            $t->date('production_date');
            $t->unsignedInteger('target_porsi');
            $t->unsignedInteger('actual_porsi')->nullable();
            $t->decimal('total_cost', 15, 2)->nullable();
            $t->enum('status', [
                'draft', 'confirmed', 'processing', 'ready', 'distributed', 'completed', 'cancelled',
            ])->default('draft');
            $t->boolean('is_locked')->default(false);
            $t->timestamps();

            $t->index(['id_sppg', 'status']);
            $t->index('production_date');
        });

        DB::statement('ALTER TABLE production_batches ADD CONSTRAINT production_batches_porsi_chk CHECK (target_porsi > 0 AND (actual_porsi IS NULL OR actual_porsi <= target_porsi))');
    }

    public function down(): void
    {
        Schema::dropIfExists('production_batches');
    }
};
