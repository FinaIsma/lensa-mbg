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
        Schema::create('pre_distribution_checks', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'production_batch_id', 'production_batches');
            Keys::user($t, 'checked_by');
            $t->unsignedInteger('portions_ready');
            $t->text('reported_condition')->nullable();
            $t->text('note')->nullable();
            $t->timestamp('checked_at')->useCurrent();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pre_distribution_checks');
    }
};
