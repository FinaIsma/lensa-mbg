<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pre_distribution_checks', function (Blueprint $table) {
            $table->string('form_type', 50)->default('makanan_siap_santap');
            $table->string('nutritionist_name', 150)->nullable();
            $table->foreignId('nutritionist_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('consume_by')->nullable(); // Waktu Kritis: Batas Maksimal Dimakan
            $table->timestamp('estimated_distribution_at')->nullable(); // Jam Estimasi Distribusi (dibedakan dgn jam uji fisik checked_at)
            $table->json('physical_test_matrix')->nullable(); // Matriks Uji Fisik Organoleptik
            $table->string('physical_test_status', 30)->default('passed'); // passed / warning / failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_distribution_checks', function (Blueprint $table) {
            $table->dropForeign(['nutritionist_id']);
            $table->dropColumn([
                'form_type',
                'nutritionist_name',
                'nutritionist_id',
                'consume_by',
                'estimated_distribution_at',
                'physical_test_matrix',
                'physical_test_status',
            ]);
        });
    }
};
