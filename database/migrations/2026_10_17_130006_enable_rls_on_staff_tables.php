<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tabel = ['staff', 'kepala_change_requests'];

    public function up(): void
    {
        foreach ($this->tabel as $t) {
            DB::statement("ALTER TABLE \"{$t}\" ENABLE ROW LEVEL SECURITY");
        }
    }

    public function down(): void
    {
        foreach ($this->tabel as $t) {
            DB::statement("ALTER TABLE \"{$t}\" DISABLE ROW LEVEL SECURITY");
        }
    }
};
