<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $allActions = [
        'confirm', 'void', 'cancel',
        'correction_request', 'correction_approve', 'correction_reject', 'correction_apply',
        'sppg_verify', 'sppg_reject', 'sppg_activate', 'sppg_deactivate', 'staff_replace',
        'kepala_change_request', 'kepala_change_approve', 'kepala_change_reject',
        // Aksi baru untuk Menu & Resep dan modul lainnya
        'create', 'update', 'toggle_status', 'create_version',
    ];

    public function up(): void
    {
        DB::statement("DO \$\$ DECLARE r record; BEGIN
            FOR r IN SELECT conname FROM pg_constraint
                     WHERE conrelid = 'audit_trails'::regclass AND contype = 'c' AND pg_get_constraintdef(oid) ILIKE '%action%'
            LOOP EXECUTE format('ALTER TABLE audit_trails DROP CONSTRAINT %I', r.conname); END LOOP;
        END \$\$");

        $in = implode(', ', array_map(fn ($a) => "'{$a}'", $this->allActions));
        DB::statement("ALTER TABLE audit_trails ADD CONSTRAINT audit_trails_action_check CHECK (action IN ({$in}))");
    }

    public function down(): void
    {
        $oldActions = [
            'confirm', 'void', 'cancel',
            'correction_request', 'correction_approve', 'correction_reject', 'correction_apply',
            'sppg_verify', 'sppg_reject', 'sppg_activate', 'sppg_deactivate', 'staff_replace',
            'kepala_change_request', 'kepala_change_approve', 'kepala_change_reject',
        ];

        DB::statement("DO \$\$ DECLARE r record; BEGIN
            FOR r IN SELECT conname FROM pg_constraint
                     WHERE conrelid = 'audit_trails'::regclass AND contype = 'c' AND pg_get_constraintdef(oid) ILIKE '%action%'
            LOOP EXECUTE format('ALTER TABLE audit_trails DROP CONSTRAINT %I', r.conname); END LOOP;
        END \$\$");

        $in = implode(', ', array_map(fn ($a) => "'{$a}'", $oldActions));
        DB::statement("ALTER TABLE audit_trails ADD CONSTRAINT audit_trails_action_check CHECK (action IN ({$in}))");
    }
};
