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
        Schema::create('distribution_destinations', function (Blueprint $t) {
            $t->id();
            Keys::sppg($t);
            $t->string('nama', 150);
            $t->string('tipe', 50)->nullable(); // sekolah / posyandu / lainnya
            $t->text('alamat')->nullable();
            $t->unsignedInteger('jumlah_penerima')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distribution_destinations');
    }
};
