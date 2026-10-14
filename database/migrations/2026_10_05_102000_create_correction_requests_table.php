<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('correction_requests')) {
            Schema::create('correction_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('id_sppg')->nullable()->constrained('sppg', 'id_sppg')->nullOnDelete();
                $table->foreignId('requested_by')->nullable()->constrained('users', 'id_user')->nullOnDelete();
                $table->foreignId('reviewed_by')->nullable()->constrained('users', 'id_user')->nullOnDelete();
                $table->string('request_no')->nullable();
                $table->string('target_table')->nullable();
                $table->unsignedBigInteger('target_id')->nullable();
                $table->string('field_name')->nullable();
                $table->text('old_value')->nullable();
                $table->text('new_value')->nullable();
                $table->text('reason')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('review_note')->nullable();
                $table->timestamp('applied_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('correction_requests');
    }
};
