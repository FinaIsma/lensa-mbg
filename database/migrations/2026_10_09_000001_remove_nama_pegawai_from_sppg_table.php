<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sppg', function (Blueprint $table) {
            if (Schema::hasColumn('sppg', 'nama_pegawai')) {
                $table->dropColumn('nama_pegawai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sppg', function (Blueprint $table) {
            if (!Schema::hasColumn('sppg', 'nama_pegawai')) {
                $table->string('nama_pegawai')->nullable()->after('nama_sppg');
            }
        });
    }
};
