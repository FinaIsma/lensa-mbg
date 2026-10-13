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
        Schema::create('materials', function (Blueprint $t) {
            $t->id();
            Keys::sppg($t);
            $t->string('kode', 30);
            $t->string('nama', 150);
            $t->string('kategori', 80)->nullable();
            $t->string('satuan', 20); // kg / liter / pcs
            $t->decimal('min_stock', 14, 3)->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->unique(['id_sppg', 'kode']);
        });

        DB::statement('ALTER TABLE materials ADD CONSTRAINT materials_min_stock_chk CHECK (min_stock >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
