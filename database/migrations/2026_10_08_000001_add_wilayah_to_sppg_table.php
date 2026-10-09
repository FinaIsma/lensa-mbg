<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sppg', function (Blueprint $table) {
            if (!Schema::hasColumn('sppg', 'provinsi')) {
                $table->string('provinsi')->nullable()->after('alamat_sppg');
            }
            if (!Schema::hasColumn('sppg', 'kabupaten_kota')) {
                $table->string('kabupaten_kota')->nullable()->after('provinsi');
            }
            if (!Schema::hasColumn('sppg', 'kecamatan')) {
                $table->string('kecamatan')->nullable()->after('kabupaten_kota');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sppg', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('sppg', 'provinsi')) {
                $columns[] = 'provinsi';
            }
            if (Schema::hasColumn('sppg', 'kabupaten_kota')) {
                $columns[] = 'kabupaten_kota';
            }
            if (Schema::hasColumn('sppg', 'kecamatan')) {
                $columns[] = 'kecamatan';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
