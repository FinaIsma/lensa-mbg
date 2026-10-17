<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Hapus semua CHECK pada $tabel yang menyinggung $kolom; kembalikan true jika ada yang dihapus. */
    private function dropChecks(string $tabel, string $kolom): void
    {
        DB::statement("DO \$\$ DECLARE r record; BEGIN
            FOR r IN SELECT conname FROM pg_constraint
                     WHERE conrelid = '{$tabel}'::regclass AND contype = 'c' AND pg_get_constraintdef(oid) ILIKE '%{$kolom}%'
            LOOP EXECUTE format('ALTER TABLE %I DROP CONSTRAINT %I', '{$tabel}', r.conname); END LOOP;
        END \$\$");
    }

    public function up(): void
    {
        $u = Keys::USER_TABLE;
        $s = Keys::SPPG_TABLE;

        $this->dropChecks($u, 'role');
        DB::statement("UPDATE \"{$u}\" SET role = 'admin' WHERE role = 'admin_sistem'");
        DB::statement("UPDATE \"{$u}\" SET role = 'kepala_sppg' WHERE role = 'admin_sppg'");
        DB::statement("ALTER TABLE \"{$u}\" ADD CONSTRAINT {$u}_role_check CHECK (role IN ('admin', 'kepala_sppg')) NOT VALID");

        $this->dropChecks($s, 'status');
        DB::statement("ALTER TABLE \"{$s}\" ADD CONSTRAINT {$s}_status_check CHECK (status IN ('menunggu_verifikasi', 'aktif', 'nonaktif', 'perubahan_data'))");
    }

    public function down(): void
    {
        $u = Keys::USER_TABLE;
        $s = Keys::SPPG_TABLE;

        $this->dropChecks($s, 'status');
        DB::statement("UPDATE \"{$s}\" SET status = 'aktif' WHERE status = 'perubahan_data'");
        DB::statement("ALTER TABLE \"{$s}\" ADD CONSTRAINT {$s}_status_check CHECK (status IN ('menunggu_verifikasi', 'aktif', 'nonaktif'))");

        $this->dropChecks($u, 'role');
        DB::statement("UPDATE \"{$u}\" SET role = 'admin_sistem' WHERE role = 'admin'");
        DB::statement("UPDATE \"{$u}\" SET role = 'admin_sppg' WHERE role = 'kepala_sppg'");
    }
};
