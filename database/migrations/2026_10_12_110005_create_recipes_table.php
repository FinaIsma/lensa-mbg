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
        Schema::create('recipes', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'menu_id', 'menus');
            $t->enum('ukuran_porsi', ['besar', 'kecil']);
            $t->unsignedInteger('version')->default(1);
            $t->enum('status', ['active', 'archived'])->default('active');
            $t->timestamps();

            $t->unique(['menu_id', 'ukuran_porsi', 'version']);
        });

        DB::statement("CREATE UNIQUE INDEX recipes_one_active_per_menu_size ON recipes (menu_id, ukuran_porsi) WHERE status = 'active'");
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
