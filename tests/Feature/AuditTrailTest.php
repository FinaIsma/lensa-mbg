<?php

namespace Tests\Feature;

use App\Models\AuditTrail;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $adminUser;

    private User $kepalaUser;

    private User $kepalaUserLain;

    private Staff $staffUser;

    private Sppg $sppg1;

    private Sppg $sppg2;

    public function createApplication()
    {
        $app = parent::createApplication();
        $app['config']->set('database.default', 'pgsql');
        $app['config']->set('database.connections.pgsql.database', 'postgres');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Akun Admin Sistem
        $this->adminUser = User::create([
            'nama' => 'Admin Sistem Test '.uniqid(),
            'email' => 'admin_'.uniqid().'@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        // 2. Akun Kepala SPPG 1 & Data SPPG 1
        $this->kepalaUser = User::create([
            'nama' => 'Kepala SPPG 1 '.uniqid(),
            'email' => 'kepala1_'.uniqid().'@test.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg1 = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Malang '.uniqid(),
            'no_telepon' => '081234567891',
            'alamat_sppg' => 'Jl. Bedali Indah No. 12',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        // 3. Akun Kepala SPPG 2 & Data SPPG 2 (untuk uji isolasi multi-tenant)
        $this->kepalaUserLain = User::create([
            'nama' => 'Kepala SPPG 2 '.uniqid(),
            'email' => 'kepala2_'.uniqid().'@test.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg2 = Sppg::create([
            'id_user' => $this->kepalaUserLain->id_user,
            'nama_sppg' => 'SPPG Surabaya '.uniqid(),
            'no_telepon' => '081234567892',
            'alamat_sppg' => 'Jl. Raya Darmo No. 88',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kota Surabaya',
            'kecamatan' => 'Wonokromo',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        // 4. Akun Staff di SPPG 1
        $this->staffUser = Staff::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'nama' => 'Staff Dapur Test '.uniqid(),
            'email' => 'staff_'.uniqid().'@test.com',
            'password' => Hash::make('password123'),
            'no_telepon' => '081234567893',
            'status' => 'aktif',
        ]);
    }

    // =========================================================================
    // SKENARIO 1: Menguji Otorisasi dan Kontrol Akses Multi-tenancy
    // =========================================================================

    /**
     * 1.1 Mengakses halaman Audit Trail tanpa login (Guest).
     */
    public function test_guest_cannot_access_audit_trail_pages(): void
    {
        $this->get(route('admin.audit'))
            ->assertRedirect(route('login'));

        $this->get(route('sppg.audit'))
            ->assertRedirect(route('login'));
    }

    /**
     * 1.2 Mengakses halaman Audit Trail menggunakan akun Staff.
     */
    public function test_staff_is_redirected_away_from_audit_trail_pages(): void
    {
        $this->actingAs($this->staffUser, 'staff')
            ->get(route('admin.audit'))
            ->assertRedirect(route('staff.dashboard'));

        $this->actingAs($this->staffUser, 'staff')
            ->get(route('sppg.audit'))
            ->assertRedirect(route('staff.dashboard'));
    }

    /**
     * 1.3 Mengakses halaman Audit Trail menggunakan akun Kepala SPPG.
     */
    public function test_kepala_sppg_can_access_own_audit_trail_but_cannot_access_admin_audit(): void
    {
        // Kepala SPPG dilarang mengakses rute admin
        $this->actingAs($this->kepalaUser)
            ->get(route('admin.audit'))
            ->assertRedirect(route('sppg.dashboard'));

        // Kepala SPPG dapat mengakses rute SPPG miliknya
        $response = $this->actingAs($this->kepalaUser)
            ->get(route('sppg.audit'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
        $response->assertSee($this->sppg1->nama_sppg);
    }

    /**
     * 1.4 Mengakses halaman Audit Trail menggunakan akun Admin Sistem.
     */
    public function test_admin_can_access_audit_trail_page(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
        $response->assertSee('Seluruh SPPG');
    }

    // =========================================================================
    // SKENARIO 2: Menguji Fitur Pencarian, Filter Data, dan Pagination
    // =========================================================================

    /**
     * 2.1 Filter riwayat audit berdasarkan modul (misal: Modul Menu).
     */
    public function test_filter_audit_by_module(): void
    {
        $markerMenu = 'MenuTestItem_'.uniqid();
        $markerSupplier = 'SupplierTestItem_'.uniqid();

        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 1001,
            'new_values' => ['nama' => $markerMenu],
            'created_at' => now(),
        ]);

        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'create',
            'auditable_type' => 'suppliers',
            'auditable_id' => 2001,
            'new_values' => ['nama' => $markerSupplier],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit', ['modul' => 'Menu']));

        $response->assertStatus(200);
        $response->assertSee($markerMenu);
        $response->assertDontSee($markerSupplier);
    }

    /**
     * 2.2 Filter riwayat audit berdasarkan rentang tanggal yang valid.
     */
    public function test_filter_audit_by_valid_date_range(): void
    {
        $markerTarget = 'AuditTarget_'.uniqid();
        $markerOutside = 'AuditOutside_'.uniqid();

        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 1002,
            'new_values' => ['nama' => $markerTarget],
            'created_at' => Carbon::parse('2026-10-15 10:00:00'),
        ]);

        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 1003,
            'new_values' => ['nama' => $markerOutside],
            'created_at' => Carbon::parse('2026-10-01 10:00:00'),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit', [
                'sppg' => $this->sppg1->id_sppg,
                'dari' => '2026-10-10',
                'sampai' => '2026-10-20',
            ]));

        $response->assertStatus(200);
        $response->assertSee($markerTarget);
        $response->assertDontSee($markerOutside);
    }

    /**
     * 2.3 Filter riwayat audit dengan rentang tanggal terbalik (tanggal dari > sampai).
     */
    public function test_filter_audit_with_reversed_date_range(): void
    {
        $markerTarget = 'AuditReversed_'.uniqid();

        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 1004,
            'new_values' => ['nama' => $markerTarget],
            'created_at' => Carbon::parse('2026-10-15 10:00:00'),
        ]);

        // 'dari' sengaja lebih besar daripada 'sampai'
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit', [
                'sppg' => $this->sppg1->id_sppg,
                'dari' => '2026-10-20',
                'sampai' => '2026-10-10',
            ]));

        $response->assertStatus(200);
        $response->assertSee($markerTarget);
    }

    /**
     * 2.4 Pencarian log aktivitas berdasarkan kata kunci nama pengguna atau kode audit.
     */
    public function test_search_audit_by_username_or_code(): void
    {
        $uniqueActorName = 'PenggunaKhusus_'.uniqid();
        $otherActorName = 'PenggunaLain_'.uniqid();

        $auditMatch = AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $uniqueActorName,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 1005,
            'new_values' => ['nama' => 'Test'],
            'created_at' => now(),
        ]);

        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $otherActorName,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 1006,
            'new_values' => ['nama' => 'Test 2'],
            'created_at' => now(),
        ]);

        // Cari berdasarkan nama aktor
        $responseUser = $this->actingAs($this->adminUser)
            ->get(route('admin.audit', ['q' => $uniqueActorName]));

        $responseUser->assertStatus(200);
        $responseUser->assertSee($uniqueActorName);
        $responseUser->assertDontSee($otherActorName);

        // Cari berdasarkan kode audit yang dihasilkan
        $responseCode = $this->actingAs($this->adminUser)
            ->get(route('admin.audit', ['q' => $auditMatch->kode_audit]));

        $responseCode->assertStatus(200);
        $responseCode->assertSee($uniqueActorName);
    }

    /**
     * 2.5 Berpindah halaman (pagination) dengan mempertahankan query filter aktif.
     */
    public function test_pagination_preserves_active_filter_queries(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            AuditTrail::create([
                'id_sppg' => $this->sppg1->id_sppg,
                'actor_type' => 'user',
                'actor_id' => $this->kepalaUser->id_user,
                'actor_nama' => $this->kepalaUser->nama,
                'action' => 'create',
                'auditable_type' => 'menus',
                'auditable_id' => 3000 + $i,
                'new_values' => ['nama' => 'PaginationItem_'.$i],
                'created_at' => now()->subMinutes(15 - $i),
            ]);
        }

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit', [
                'modul' => 'Menu',
                'page' => 2,
            ]));

        $response->assertStatus(200);
        // Link pagination harus tetap mempertahankan filter ?modul=Menu
        $response->assertSee('modul=Menu');
    }

    // =========================================================================
    // SKENARIO 3: Menguji Integritas, Immutabilitas, dan Drawer Detail Perubahan
    // =========================================================================

    /**
     * 3.1 Membuka panel drawer detail aktivitas mutasi biasa (perbandingan data lama & baru).
     */
    public function test_drawer_detail_renders_old_and_new_mutation_values(): void
    {
        $oldMarker = 'OldVal_'.uniqid();
        $newMarker = 'NewVal_'.uniqid();

        $audit = AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'update',
            'auditable_type' => 'menus',
            'auditable_id' => 4001,
            'old_values' => ['nama_menu' => $oldMarker],
            'new_values' => ['nama_menu' => $newMarker],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit'));

        $response->assertStatus(200);
        $response->assertSee('template id="detail-'.$audit->id.'"', false);
        $response->assertSee('Perubahan Data');
        $response->assertSee($oldMarker);
        $response->assertSee($newMarker);
    }

    /**
     * 3.2 Membuka panel drawer detail aktivitas yang terhubung dengan pengajuan koreksi dan bukti lampiran.
     */
    public function test_drawer_detail_renders_correction_request_and_evidence(): void
    {
        $uniqueReason = 'Kesalahan timbang fisik bahan '.uniqid();
        $uniqueEvidence = 'evidences/nota_test_'.uniqid().'.jpg';

        $correctionId = DB::table('correction_requests')->insertGetId([
            'id_sppg' => $this->sppg1->id_sppg,
            'requested_by' => $this->staffUser->id,
            'request_no' => 'CR-'.uniqid(),
            'target_table' => 'receivings',
            'target_id' => 5001,
            'field_name' => 'qty_received',
            'old_value' => '10',
            'new_value' => '15',
            'reason' => $uniqueReason,
            'status' => 'approved',
            'requested_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('evidences')->insert([
            'uploaded_by' => $this->staffUser->id,
            'evidenceable_type' => 'correction_requests',
            'evidenceable_id' => $correctionId,
            'file_path' => $uniqueEvidence,
            'file_type' => 'image/jpeg',
            'description' => 'Foto Nota Koreksi Fisik',
            'is_public' => false,
            'uploaded_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $audit = AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'correction_request_id' => $correctionId,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'correction_apply',
            'auditable_type' => 'receivings',
            'auditable_id' => 5001,
            'old_values' => ['qty' => 10],
            'new_values' => ['qty' => 15],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit'));

        $response->assertStatus(200);
        $response->assertSee('template id="detail-'.$audit->id.'"', false);
        $response->assertSee('Alasan Perubahan');
        $response->assertSee($uniqueReason);
        $response->assertSee(basename($uniqueEvidence));
    }

    /**
     * 3.3 Menguji proteksi immutabilitas data log (pencegahan manipulasi update dan delete record).
     */
    public function test_immutability_protection_prevents_update_and_delete(): void
    {
        $audit = AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $this->kepalaUser->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 6001,
            'new_values' => ['nama' => 'Immutable Menu'],
            'created_at' => now(),
        ]);

        // Uji Pencegahan Update
        try {
            $audit->update(['actor_nama' => 'Hacker Name']);
            $this->fail('Expected LogicException was not thrown on update.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('Audit trail tidak boleh diubah', $e->getMessage());
        }

        // Uji Pencegahan Delete
        try {
            $audit->delete();
            $this->fail('Expected LogicException was not thrown on delete.');
        } catch (LogicException $e) {
            $this->assertStringContainsString('Audit trail tidak boleh dihapus', $e->getMessage());
        }

        // Verifikasi bahwa record tetap utuh di database
        $this->assertDatabaseHas('audit_trails', [
            'id' => $audit->id,
            'actor_nama' => $this->kepalaUser->nama,
        ]);
    }

    // =========================================================================
    // SKENARIO 4: Menguji Fungsionalitas Ekspor Data ke Format CSV dan PDF
    // =========================================================================

    /**
     * 4.1 Mengunduh data audit trail ke format CSV.
     */
    public function test_export_audit_trail_to_csv(): void
    {
        $audit = AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->adminUser->id_user,
            'actor_nama' => $this->adminUser->nama,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 7001,
            'new_values' => ['nama' => 'CSV Export Menu'],
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit.export.csv'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('attachment; filename=audit_trail_', $response->headers->get('Content-Disposition') ?? '');

        $csvContent = $response->streamedContent();
        $this->assertStringContainsString('Kode Audit', $csvContent);
        $this->assertStringContainsString('Unit SPPG', $csvContent);
        $this->assertStringContainsString($audit->kode_audit, $csvContent);
    }

    /**
     * 4.2 Membuka laporan resmi audit trail untuk dicetak / disimpan ke PDF.
     */
    public function test_export_audit_trail_to_printable_pdf(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit.export.pdf'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.audit-trail-print');
        $response->assertSee('Pratinjau Dokumen Audit Trail');
        $response->assertSee('Seluruh SPPG');
    }

    /**
     * 4.3 Verifikasi keamanan isolasi data saat ekspor CSV oleh akun Kepala SPPG.
     */
    public function test_data_isolation_on_csv_export_by_kepala_sppg(): void
    {
        $markerSppg1 = 'UniqueAuditSPPG1_'.uniqid();
        $markerSppg2 = 'SecretAuditSPPG2_'.uniqid();

        // Data audit untuk SPPG 1
        AuditTrail::create([
            'id_sppg' => $this->sppg1->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUser->id_user,
            'actor_nama' => $markerSppg1,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 8001,
            'new_values' => ['nama' => 'Menu 1'],
            'created_at' => now(),
        ]);

        // Data audit untuk SPPG 2 (milik SPPG lain)
        AuditTrail::create([
            'id_sppg' => $this->sppg2->id_sppg,
            'actor_type' => 'user',
            'actor_id' => $this->kepalaUserLain->id_user,
            'actor_nama' => $markerSppg2,
            'action' => 'create',
            'auditable_type' => 'menus',
            'auditable_id' => 8002,
            'new_values' => ['nama' => 'Menu 2'],
            'created_at' => now(),
        ]);

        // Kepala SPPG 1 mengunduh CSV melalui rutenya
        $response = $this->actingAs($this->kepalaUser)
            ->get(route('sppg.audit.export.csv'));

        $response->assertStatus(200);
        $csvContent = $response->streamedContent();

        // Verifikasi isolasi: Data SPPG 1 ada di CSV, sedangkan data SPPG 2 TIDAK BOLEH bocor
        $this->assertStringContainsString($markerSppg1, $csvContent);
        $this->assertStringNotContainsString($markerSppg2, $csvContent);
    }
}
