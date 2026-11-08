<?php

namespace Tests\Unit;

use App\Models\AuditTrail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AuditTrailUnitTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql.database', 'postgres');

        return $app;
    }

    /**
     * U1: Memverifikasi accessor kode_audit menghasilkan format "AUD-xxxx" secara konsisten.
     */
    public function test_kode_audit_accessor_formats_id_correctly(): void
    {
        $audit = new AuditTrail;
        $audit->id = 7;
        $this->assertSame('AUD-0007', $audit->kode_audit);

        $audit->id = 142;
        $this->assertSame('AUD-0142', $audit->kode_audit);
    }

    /**
     * U2: Memverifikasi accessor modul memetakan nama tabel ke nama modul yang mudah dibaca.
     */
    public function test_modul_accessor_maps_table_names_to_readable_module_names(): void
    {
        $auditMenu = new AuditTrail(['auditable_type' => 'menus']);
        $this->assertSame('Menu', $auditMenu->modul);

        $auditPembelian = new AuditTrail(['auditable_type' => 'purchases']);
        $this->assertSame('Pembelian', $auditPembelian->modul);

        $auditSppg = new AuditTrail(['auditable_type' => 'sppg']);
        $this->assertSame('SPPG', $auditSppg->modul);
    }

    /**
     * U3: Memverifikasi accessor aktivitas_label dan nada (warna badge) berdasarkan aksi.
     */
    public function test_aktivitas_label_and_badge_tone_mapping(): void
    {
        // Aksi status OK (badge hijau)
        $auditConfirm = new AuditTrail(['action' => 'confirm']);
        $this->assertSame('Mengonfirmasi', $auditConfirm->aktivitas_label);
        $this->assertSame('ok', $auditConfirm->nada);

        // Aksi status BAD (badge merah)
        $auditVoid = new AuditTrail(['action' => 'void']);
        $this->assertSame('Membatalkan (void)', $auditVoid->aktivitas_label);
        $this->assertSame('bad', $auditVoid->nada);

        // Aksi status INFO (badge biru/netral)
        $auditUpdate = new AuditTrail(['action' => 'update']);
        $this->assertSame('Mengubah data', $auditUpdate->aktivitas_label);
        $this->assertSame('info', $auditUpdate->nada);
    }

    /**
     * U4: Memverifikasi helper daftarModul mengembalikan daftar modul unik tanpa duplikasi.
     */
    public function test_daftar_modul_returns_unique_list(): void
    {
        $daftar = AuditTrail::daftarModul();

        $this->assertIsArray($daftar);
        $this->assertContains('Menu', $daftar);
        $this->assertContains('Pembelian', $daftar);
        $this->assertContains('SPPG', $daftar);
        // Pastikan tidak ada duplikasi
        $this->assertSame($daftar, array_unique($daftar));
    }

    /**
     * U5: Memverifikasi proteksi immutabilitas pada model AuditTrail (mencegah update & delete).
     */
    public function test_immutability_prevents_record_update_and_delete(): void
    {
        $user = User::create([
            'nama' => 'Test User Immutability',
            'email' => 'unit_'.uniqid().'@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $audit = AuditTrail::create([
            'actor_type' => 'user',
            'actor_id' => $user->id_user,
            'actor_nama' => $user->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 999,
            'new_values' => ['nama' => 'Menu Asli'],
            'created_at' => now(),
        ]);

        // Coba Update -> harus melempar LogicException
        try {
            $audit->update(['actor_nama' => 'Manipulasi']);
            $this->fail('Expected LogicException was not thrown on update.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('Audit trail tidak boleh diubah', $e->getMessage());
        }

        // Coba Delete -> harus melempar LogicException
        try {
            $audit->delete();
            $this->fail('Expected LogicException was not thrown on delete.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('Audit trail tidak boleh dihapus', $e->getMessage());
        }
    }
}
