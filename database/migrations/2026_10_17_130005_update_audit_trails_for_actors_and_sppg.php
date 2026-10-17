<?php

use App\Support\Keys;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $aksiLama = [
        'confirm', 'void', 'cancel',
        'correction_request', 'correction_approve', 'correction_reject', 'correction_apply',
    ];

    private array $aksiBaru = [
        'sppg_verify', 'sppg_reject', 'sppg_activate', 'sppg_deactivate', 'staff_replace',
        'kepala_change_request', 'kepala_change_approve', 'kepala_change_reject',
    ];

    private function setAksi(array $daftar): void
    {
        DB::statement("DO \$\$ DECLARE r record; BEGIN
            FOR r IN SELECT conname FROM pg_constraint
                     WHERE conrelid = 'audit_trails'::regclass AND contype = 'c' AND pg_get_constraintdef(oid) ILIKE '%action%'
            LOOP EXECUTE format('ALTER TABLE audit_trails DROP CONSTRAINT %I', r.conname); END LOOP;
        END \$\$");

        $in = implode(', ', array_map(fn ($a) => "'{$a}'", $daftar));
        DB::statement("ALTER TABLE audit_trails ADD CONSTRAINT audit_trails_action_check CHECK (action IN ({$in}))");
    }

    public function up(): void
    {
        $u = Keys::USER_TABLE; $upk = Keys::USER_PK;
        $s = Keys::SPPG_TABLE; $spk = Keys::SPPG_PK; $sown = Keys::SPPG_OWNER;

        DB::statement('ALTER TABLE audit_trails
            ADD COLUMN id_sppg bigint NULL,
            ADD COLUMN actor_type varchar(10) NOT NULL DEFAULT \'user\',
            ADD COLUMN actor_id bigint NULL,
            ADD COLUMN actor_nama varchar(150) NULL');

        DB::statement('UPDATE audit_trails SET actor_id = id_user');
        DB::statement("UPDATE audit_trails a SET actor_nama = x.nama FROM \"{$u}\" x WHERE x.\"{$upk}\" = a.id_user");
        DB::statement("UPDATE audit_trails a SET id_sppg = p.\"{$spk}\" FROM \"{$s}\" p WHERE p.\"{$sown}\" = a.id_user");

        DB::statement('ALTER TABLE audit_trails ALTER COLUMN actor_id SET NOT NULL');
        DB::statement('ALTER TABLE audit_trails DROP CONSTRAINT IF EXISTS audit_trails_id_user_foreign');
        DB::statement('ALTER TABLE audit_trails DROP COLUMN id_user');

        DB::statement("ALTER TABLE audit_trails ADD CONSTRAINT audit_trails_actor_type_check CHECK (actor_type IN ('user', 'staff'))");
        DB::statement("ALTER TABLE audit_trails ADD CONSTRAINT audit_trails_id_sppg_foreign FOREIGN KEY (id_sppg) REFERENCES \"{$s}\" (\"{$spk}\") ON DELETE RESTRICT");
        DB::statement('CREATE INDEX audit_trails_id_sppg_created_at_index ON audit_trails (id_sppg, created_at)');
        DB::statement('CREATE INDEX audit_trails_actor_index ON audit_trails (actor_type, actor_id)');

        $this->setAksi(array_merge($this->aksiLama, $this->aksiBaru));
    }

    public function down(): void
    {
        $u = Keys::USER_TABLE; $upk = Keys::USER_PK;

        // Baris dengan aksi baru atau pelaku staff tidak punya padanan di skema lama: dihapus agar rollback bisa jalan.
        DB::statement("DELETE FROM audit_trails WHERE actor_type = 'staff' OR action IN ('" . implode("', '", $this->aksiBaru) . "')");

        $this->setAksi($this->aksiLama);

        DB::statement('ALTER TABLE audit_trails ADD COLUMN id_user bigint NULL');
        DB::statement('UPDATE audit_trails SET id_user = actor_id');
        DB::statement('ALTER TABLE audit_trails ALTER COLUMN id_user SET NOT NULL');
        DB::statement("ALTER TABLE audit_trails ADD CONSTRAINT audit_trails_id_user_foreign FOREIGN KEY (id_user) REFERENCES \"{$u}\" (\"{$upk}\") ON DELETE RESTRICT");
        DB::statement('CREATE INDEX audit_trails_id_user_index ON audit_trails (id_user)');

        DB::statement('ALTER TABLE audit_trails DROP COLUMN actor_nama, DROP COLUMN actor_id, DROP COLUMN actor_type, DROP COLUMN id_sppg');
    }
};
