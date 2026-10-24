<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Hapus semua CHECK pada $tabel yang menyinggung $kolom. */
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
        $userTable = Keys::USER_TABLE;
        $sppgTable = Keys::SPPG_TABLE;

        // 1. MODIFIKASI TABEL STAFF (Multi-user & Status Persetujuan)
        // a. Drop partial unique index yang sebelumnya membatasi hanya 1 staff aktif/menunggu per SPPG
        DB::statement('DROP INDEX IF EXISTS staff_satu_berjalan_per_sppg');

        // b. Tambah kolom catatan_admin jika belum ada
        if (! Schema::hasColumn('staff', 'catatan_admin')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->text('catatan_admin')->nullable()->after('status');
            });
        }

        // c. Update check constraint status staff (mendukung menunggu_persetujuan dan ditolak)
        $this->dropChecks('staff', 'status');
        DB::statement("UPDATE \"staff\" SET status = 'menunggu_persetujuan' WHERE status = 'menunggu'");
        DB::statement("ALTER TABLE \"staff\" ADD CONSTRAINT staff_status_check CHECK (status IN ('menunggu', 'menunggu_persetujuan', 'aktif', 'nonaktif', 'ditolak')) NOT VALID");

        // 2. MODIFIKASI TABEL USERS (Status Akun Kepala SPPG & Admin)
        if (! Schema::hasColumn($userTable, 'status')) {
            Schema::table($userTable, function (Blueprint $table) {
                $table->string('status', 30)->default('aktif')->after('role');
            });
        }

        // Sinkronisasi status user kepala_sppg dari status SPPG-nya
        DB::statement("UPDATE \"{$userTable}\" u
            SET status = CASE 
                WHEN s.status IN ('aktif', 'perubahan_data') THEN 'aktif'
                WHEN s.status = 'menunggu_verifikasi' THEN 'menunggu_verifikasi'
                ELSE 'nonaktif'
            END
            FROM \"{$sppgTable}\" s
            WHERE s.id_user = u.id_user AND u.role = 'kepala_sppg'");

        $this->dropChecks($userTable, 'status');
        DB::statement("ALTER TABLE \"{$userTable}\" ADD CONSTRAINT {$userTable}_status_check CHECK (status IN ('menunggu_verifikasi', 'aktif', 'nonaktif')) NOT VALID");

        // 3. MODIFIKASI TABEL SPPG (Pemisahan Status Operasional vs Status Verifikasi)
        if (! Schema::hasColumn($sppgTable, 'status_verifikasi')) {
            Schema::table($sppgTable, function (Blueprint $table) {
                $table->string('status_verifikasi', 50)->default('menunggu_verifikasi')->after('status');
            });
        }

        // Migrasi data status verifikasi dari status lama
        DB::statement("UPDATE \"{$sppgTable}\" SET status_verifikasi = CASE 
            WHEN status = 'aktif' THEN 'terverifikasi'
            WHEN status = 'perubahan_data' THEN 'perubahan'
            WHEN status = 'menunggu_verifikasi' THEN 'menunggu_verifikasi'
            ELSE 'ditolak'
        END");

        // Standarisasi status operasional SPPG menjadi 'aktif' atau 'nonaktif'
        DB::statement("UPDATE \"{$sppgTable}\" SET status = 'aktif' WHERE status = 'perubahan_data'");
        DB::statement("UPDATE \"{$sppgTable}\" SET status = 'nonaktif' WHERE status = 'menunggu_verifikasi'");

        $this->dropChecks($sppgTable, 'status');
        $this->dropChecks($sppgTable, 'status_verifikasi');

        DB::statement("ALTER TABLE \"{$sppgTable}\" ADD CONSTRAINT {$sppgTable}_status_check CHECK (status IN ('aktif', 'nonaktif')) NOT VALID");
        DB::statement("ALTER TABLE \"{$sppgTable}\" ADD CONSTRAINT {$sppgTable}_status_verifikasi_check CHECK (status_verifikasi IN ('menunggu_verifikasi', 'terverifikasi', 'perubahan', 'ditolak')) NOT VALID");
    }

    public function down(): void
    {
        $userTable = Keys::USER_TABLE;
        $sppgTable = Keys::SPPG_TABLE;

        // Rollback SPPG
        $this->dropChecks($sppgTable, 'status');
        $this->dropChecks($sppgTable, 'status_verifikasi');

        DB::statement("UPDATE \"{$sppgTable}\" SET status = CASE 
            WHEN status_verifikasi = 'menunggu_verifikasi' THEN 'menunggu_verifikasi'
            WHEN status_verifikasi = 'perubahan' THEN 'perubahan_data'
            WHEN status = 'aktif' THEN 'aktif'
            ELSE 'nonaktif'
        END");

        DB::statement("ALTER TABLE \"{$sppgTable}\" ADD CONSTRAINT {$sppgTable}_status_check CHECK (status IN ('menunggu_verifikasi', 'aktif', 'nonaktif', 'perubahan_data')) NOT VALID");

        if (Schema::hasColumn($sppgTable, 'status_verifikasi')) {
            Schema::table($sppgTable, function (Blueprint $table) {
                $table->dropColumn('status_verifikasi');
            });
        }

        // Rollback Users
        $this->dropChecks($userTable, 'status');
        if (Schema::hasColumn($userTable, 'status')) {
            Schema::table($userTable, function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }

        // Rollback Staff
        $this->dropChecks('staff', 'status');
        DB::statement("UPDATE \"staff\" SET status = 'menunggu' WHERE status = 'menunggu_persetujuan'");
        DB::statement("ALTER TABLE \"staff\" ADD CONSTRAINT staff_status_check CHECK (status IN ('menunggu', 'aktif', 'nonaktif')) NOT VALID");

        if (Schema::hasColumn('staff', 'catatan_admin')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->dropColumn('catatan_admin');
            });
        }

        DB::statement("CREATE UNIQUE INDEX staff_satu_berjalan_per_sppg ON staff (id_sppg) WHERE status IN ('menunggu', 'aktif')");
    }
};
