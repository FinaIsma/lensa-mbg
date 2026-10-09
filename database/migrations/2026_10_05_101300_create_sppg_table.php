<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sppg', function (Blueprint $table) {
            $table->id('id_sppg');

            $table->foreignId('id_user')
                ->unique()
                ->constrained('users', 'id_user')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('nama_sppg');
            $table->string('no_telepon');
            $table->text('alamat_sppg');
            $table->string('provinsi')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('kecamatan')->nullable();

            $table->string('foto_ktp')->nullable();
            $table->string('foto_kantor_sppg')->nullable();
            $table->string('foto_surat_resmi')->nullable();

            $table->enum('status', [
                'menunggu_verifikasi',
                'aktif',
                'nonaktif'
            ])->default('menunggu_verifikasi');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sppg');
    }
};
