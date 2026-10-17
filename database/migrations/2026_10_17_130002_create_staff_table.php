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
        Schema::create('staff', function (Blueprint $t) {
            $t->id();
            Keys::sppg($t);
            $t->string('nama', 150);
            $t->string('email', 150)->unique();
            $t->string('password');
            $t->string('no_telepon', 30)->nullable();
            $t->enum('status', ['menunggu', 'aktif', 'nonaktif'])->default('menunggu');
            $t->timestamp('nonaktif_at')->nullable();
            $t->text('nonaktif_reason')->nullable();
            $t->string('remember_token', 100)->nullable();
            $t->timestamps();

            $t->index(['id_sppg', 'status']);
        });

        DB::statement("CREATE UNIQUE INDEX staff_satu_berjalan_per_sppg ON staff (id_sppg) WHERE status IN ('menunggu', 'aktif')");
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
