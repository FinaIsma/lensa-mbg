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
        Schema::create('audit_trails', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'correction_request_id', 'correction_requests', nullable: true);
            Keys::user($t, 'id_user');
            $t->enum('action', [
                'confirm', 'void', 'cancel',
                'correction_request', 'correction_approve', 'correction_reject', 'correction_apply',
            ]);
            $t->string('auditable_type', 100);
            $t->unsignedBigInteger('auditable_id');
            $t->jsonb('old_values')->nullable();
            $t->jsonb('new_values')->nullable();
            $t->timestamp('created_at')->useCurrent();

            $t->index(['auditable_type', 'auditable_id']);
            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
