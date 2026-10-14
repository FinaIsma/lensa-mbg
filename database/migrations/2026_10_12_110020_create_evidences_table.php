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
        Schema::create('evidences', function (Blueprint $t) {
            $t->id();
            Keys::user($t, 'uploaded_by');
            $t->string('evidenceable_type', 100);
            $t->unsignedBigInteger('evidenceable_id');
            $t->string('file_path', 500);
            $t->string('file_type', 50)->nullable();
            $t->text('description')->nullable();
            $t->boolean('is_public')->default(false);
            $t->timestamp('uploaded_at')->useCurrent();
            $t->timestamps();

            $t->index(['evidenceable_type', 'evidenceable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidences');
    }
};
