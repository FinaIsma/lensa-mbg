<?php

namespace Tests\Feature\KepalaSPPG;

use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

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

        $this->kepalaUser = User::create([
            'nama' => 'Test Kepala SPPG '.uniqid(),
            'email' => 'kepala_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Malang Test '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Bedali Lawang',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('sppg.staff.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_kepala_sppg_can_view_staff_management_page(): void
    {
        $uniqueEmail = 'staff_'.uniqid().'@example.com';
        $pendingEmail = 'pending_'.uniqid().'@example.com';

        // Staff aktif => aksi Nonaktifkan
        Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Kim Min-jeong',
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '081726386139',
            'status' => 'aktif',
        ]);

        // Staff belum verifikasi => aksi Detail
        Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Giselle',
            'email' => $pendingEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '081726386140',
            'status' => 'menunggu_persetujuan',
        ]);

        $response = $this->actingAs($this->kepalaUser)
            ->get(route('sppg.staff.index'));

        $response->assertStatus(200);
        $response->assertSee('Staff Manajemen');
        $response->assertSee('Kelola data akun staff');
        $response->assertSee('Total Staff');
        $response->assertSee('Status Aktif');
        $response->assertSee('Belum Verifikasi');
        $response->assertSee('Status Nonaktif');
        $response->assertSee('Kim Min-jeong');
        $response->assertSee($uniqueEmail);
        $response->assertSee('Giselle');
        $response->assertSee($pendingEmail);
        $response->assertSee('Nonaktifkan');
        $response->assertSee('Detail');
        $response->assertSee('Tambah Staff');
    }

    public function test_kepala_sppg_can_submit_tambah_staff(): void
    {
        $uniqueEmail = 'karina_'.uniqid().'@example.com';

        $postData = [
            'nama' => 'Yu Ji-min',
            'email' => $uniqueEmail,
            'no_telepon' => '081234567800',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ];

        $response = $this->actingAs($this->kepalaUser)
            ->post(route('sppg.staff.store'), $postData);

        $response->assertRedirect(route('sppg.staff.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('staff', [
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Yu Ji-min',
            'email' => $uniqueEmail,
            'status' => 'menunggu_persetujuan',
        ]);

        $this->assertEquals('perubahan', $this->sppg->fresh()->status_verifikasi);
    }

    public function test_tambah_staff_validation_fails_on_duplicate_email(): void
    {
        $uniqueEmail = 'duplicate_'.uniqid().'@example.com';

        Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Existing Staff',
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '08111111111',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->kepalaUser)
            ->post(route('sppg.staff.store'), [
                'nama' => 'New Staff',
                'email' => $uniqueEmail,
                'no_telepon' => '08222222222',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_kepala_sppg_can_view_staff_detail_json(): void
    {
        $uniqueEmail = 'winter_'.uniqid().'@example.com';

        $staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Kim Min-jeong',
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '081726386139',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->kepalaUser)
            ->get(route('sppg.staff.show', $staff->id));

        $response->assertStatus(200);
        $response->assertJson([
            'nama' => 'Kim Min-jeong',
            'email' => $uniqueEmail,
            'no_telepon' => '081726386139',
            'status' => 'aktif',
            'sppg_nama' => $this->sppg->nama_sppg,
        ]);
    }

    public function test_kepala_sppg_can_deactivate_staff(): void
    {
        $uniqueEmail = 'ningning_'.uniqid().'@example.com';

        $staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Ning Yizhuo',
            'email' => $uniqueEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '08333333333',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->kepalaUser)
            ->post(route('sppg.staff.deactivate', $staff->id), [
                'reason' => 'Mengundurkan diri',
            ]);

        $response->assertRedirect(route('sppg.staff.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'status' => 'nonaktif',
            'nonaktif_reason' => 'Mengundurkan diri',
        ]);
    }

    public function test_kepala_sppg_can_filter_staff_by_ditolak(): void
    {
        $rejectedEmail = 'rejected_'.uniqid().'@example.com';
        $activeEmail = 'active_'.uniqid().'@example.com';

        Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Ditolak',
            'email' => $rejectedEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '08999999999',
            'status' => 'ditolak',
        ]);

        Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Aktif',
            'email' => $activeEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '08888888888',
            'status' => 'aktif',
        ]);

        $response = $this->actingAs($this->kepalaUser)
            ->get(route('sppg.staff.index', ['status_verif' => 'ditolak']));

        $response->assertStatus(200);
        $response->assertSee('Staff Ditolak');
        $response->assertSee($rejectedEmail);
        $response->assertDontSee($activeEmail);
    }

    public function test_admin_can_approve_pending_staff_in_sppg_management(): void
    {
        $adminUser = User::create([
            'nama' => 'Admin Sistem '.uniqid(),
            'email' => 'admin_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $pendingEmail = 'staff_pending_'.uniqid().'@example.com';
        $pendingStaff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Menunggu',
            'email' => $pendingEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '081234567899',
            'status' => 'menunggu_persetujuan',
        ]);

        $this->sppg->update(['status_verifikasi' => 'perubahan']);

        // Admin checks SPPG index page
        $response = $this->actingAs($adminUser)->get(route('admin.sppg.index'));
        $response->assertStatus(200);
        $response->assertSee('Perubahan');
        $response->assertSee('Verifikasi');

        // Admin approves (status = 'aktif')
        $postResponse = $this->actingAs($adminUser)
            ->post(route('admin.sppg.updateStatus', $this->sppg->id_sppg), [
                'status' => 'aktif',
            ]);

        $postResponse->assertSessionHas('success');
        $this->assertEquals('aktif', $pendingStaff->fresh()->status);
        $this->assertEquals('terverifikasi', $this->sppg->fresh()->status_verifikasi);
    }

    public function test_admin_can_reject_pending_staff_in_sppg_management(): void
    {
        $adminUser = User::create([
            'nama' => 'Admin Sistem '.uniqid(),
            'email' => 'admin_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'aktif',
        ]);

        $pendingEmail = 'staff_reject_'.uniqid().'@example.com';
        $pendingStaff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Bakal Ditolak',
            'email' => $pendingEmail,
            'password' => Hash::make('password123'),
            'no_telepon' => '081234567899',
            'status' => 'menunggu_persetujuan',
        ]);

        $this->sppg->update(['status_verifikasi' => 'perubahan']);

        // Admin rejects (status = 'nonaktif')
        $postResponse = $this->actingAs($adminUser)
            ->post(route('admin.sppg.updateStatus', $this->sppg->id_sppg), [
                'status' => 'nonaktif',
                'review_note' => 'Dokumen tidak valid',
            ]);

        $postResponse->assertSessionHas('success');
        $this->assertEquals('ditolak', $pendingStaff->fresh()->status);
        $this->assertEquals('Dokumen tidak valid', $pendingStaff->fresh()->catatan_admin);
        $this->assertEquals('terverifikasi', $this->sppg->fresh()->status_verifikasi);
        $this->assertEquals('aktif', $this->sppg->fresh()->status);
    }
}
