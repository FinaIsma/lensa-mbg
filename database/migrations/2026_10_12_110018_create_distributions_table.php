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
        Schema::create('distributions', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'production_batch_id', 'production_batches');
            Keys::to($t, 'destination_id', 'distribution_destinations');
            Keys::user($t, 'recorded_by');
            $t->unsignedInteger('portions_sent');
            $t->enum('status', ['planned', 'sent', 'received', 'cancelled'])->default('planned');
            $t->timestamp('distributed_at')->nullable();
            $t->timestamps();
        });

        DB::statement('ALTER TABLE distributions ADD CONSTRAINT distributions_portions_chk CHECK (portions_sent > 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
