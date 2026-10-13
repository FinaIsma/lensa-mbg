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
        Schema::create('purchases', function (Blueprint $t) {
            $t->id();
            Keys::to($t, 'supplier_id', 'suppliers');
            Keys::sppg($t);
            Keys::user($t, 'created_by');
            $t->string('purchase_no', 40)->unique();
            $t->date('purchase_date');
            $t->enum('status', ['draft', 'ordered', 'received_partial', 'received', 'cancelled'])->default('draft');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
