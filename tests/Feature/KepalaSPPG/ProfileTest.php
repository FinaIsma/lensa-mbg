<?php

namespace Tests\Feature\KepalaSPPG;

use App\Models\Sppg;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
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
            'nama' => 'Yu Ji-Min',
            'email' => 'katarina_'.uniqid().'@gmail.com',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Malang 01',
            'no_telepon' => '081726386138',
            'alamat_sppg' => 'Jl Bedali Lawang',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);
    }

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->get(route('sppg.profile.show'));
        $response->assertRedirect(route('login'));
    }

    public function test_kepala_sppg_can_get_profile(): void
    {
        $response = $this->actingAs($this->kepalaUser)
            ->getJson(route('sppg.profile.show'));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    'nama_lengkap' => 'Yu Ji-Min',
                    'nama_sppg' => 'SPPG Malang 01',
                    'no_telepon' => '081726386138',
                ],
            ]);
    }

    public function test_kepala_sppg_can_update_password(): void
    {
        $response = $this->actingAs($this->kepalaUser)
            ->postJson(route('sppg.password.update'), [
                'current_password' => 'password123',
                'new_password' => 'newPassword123',
                'new_password_confirmation' => 'newPassword123',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Kata sandi berhasil diperbarui.',
            ]);

        $this->assertTrue(Hash::check('newPassword123', $this->kepalaUser->fresh()->password));
    }

    public function test_update_password_fails_if_current_password_is_wrong(): void
    {
        $response = $this->actingAs($this->kepalaUser)
            ->postJson(route('sppg.password.update'), [
                'current_password' => 'wrongPassword',
                'new_password' => 'newPassword123',
                'new_password_confirmation' => 'newPassword123',
            ]);

        $response->assertStatus(422);
    }

    public function test_kepala_sppg_can_submit_profile_changes(): void
    {
        $response = $this->actingAs($this->kepalaUser)
            ->postJson(route('sppg.profile.update'), [
                'nama_lengkap' => 'Yu Ji-Min Updated',
                'email' => $this->kepalaUser->email,
                'no_telepon' => '081299998888',
                'nama_sppg' => 'SPPG Malang 01 Updated',
                'alamat_sppg' => 'Jl Bedali Baru No 10',
                'provinsi' => 'Jawa Timur',
                'kabupaten_kota' => 'Kabupaten Malang',
                'kecamatan' => 'Lawang',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Staging pattern: Nilai aktif di tabel users & sppg tetap asli (tidak diubah langsung)
        $this->assertEquals('Yu Ji-Min', $this->kepalaUser->fresh()->nama);
        $this->assertEquals('SPPG Malang 01', $this->sppg->fresh()->nama_sppg);
        $this->assertEquals('perubahan', $this->sppg->fresh()->status_verifikasi);

        // Nilai baru tersimpan di antrean tabel kepala_change_requests
        $this->assertDatabaseHas('kepala_change_requests', [
            'id_sppg' => $this->sppg->id_sppg,
            'status' => 'pending',
            'nama_baru' => 'Yu Ji-Min Updated',
            'no_telepon_baru' => '081299998888',
        ]);
    }

    public function test_kepala_sppg_cannot_submit_profile_changes_when_already_pending(): void
    {
        $this->sppg->update([
            'status_verifikasi' => 'perubahan',
        ]);

        $response = $this->actingAs($this->kepalaUser)
            ->postJson(route('sppg.profile.update'), [
                'nama_lengkap' => 'Yu Ji-Min Second Edit',
                'email' => $this->kepalaUser->email,
                'no_telepon' => '081299998888',
                'nama_sppg' => 'SPPG Malang 01 Second',
                'alamat_sppg' => 'Jl Bedali Baru No 10',
                'provinsi' => 'Jawa Timur',
                'kabupaten_kota' => 'Kabupaten Malang',
                'kecamatan' => 'Lawang',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_approving_change_request_applies_staged_values(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test@example.com'],
            ['nama' => 'Admin Test', 'password' => Hash::make('secret'), 'role' => 'admin', 'status' => 'aktif']
        );

        $this->actingAs($this->kepalaUser)
            ->postJson(route('sppg.profile.update'), [
                'nama_lengkap' => 'Yu Ji-Min Approved',
                'email' => $this->kepalaUser->email,
                'no_telepon' => '081211112222',
                'nama_sppg' => 'SPPG Malang 01 Approved',
                'alamat_sppg' => 'Jl Bedali Baru No 99',
                'provinsi' => 'Jawa Timur',
                'kabupaten_kota' => 'Kabupaten Malang',
                'kecamatan' => 'Lawang',
            ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.sppg.index'))
            ->post(route('admin.sppg.updateStatus', $this->sppg->id_sppg), [
                'status' => 'aktif',
                'review_note' => 'Disetujui lengkap.',
            ]);

        $response->assertRedirect(route('admin.sppg.index'));

        // Nilai baru berhasil diterapkan ke tabel users & sppg
        $this->assertEquals('Yu Ji-Min Approved', $this->kepalaUser->fresh()->nama);
        $this->assertEquals('SPPG Malang 01 Approved', $this->sppg->fresh()->nama_sppg);
        $this->assertEquals('081211112222', $this->sppg->fresh()->no_telepon);
        $this->assertEquals('terverifikasi', $this->sppg->fresh()->status_verifikasi);
        $this->assertEquals('aktif', $this->sppg->fresh()->status);
    }

    public function test_admin_rejecting_change_request_leaves_active_values_untouched(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin_test2@example.com'],
            ['nama' => 'Admin Test 2', 'password' => Hash::make('secret'), 'role' => 'admin', 'status' => 'aktif']
        );

        $this->actingAs($this->kepalaUser)
            ->postJson(route('sppg.profile.update'), [
                'nama_lengkap' => 'Yu Ji-Min Rejected',
                'email' => $this->kepalaUser->email,
                'no_telepon' => '081299990000',
                'nama_sppg' => 'SPPG Malang 01 Rejected',
                'alamat_sppg' => 'Jl Bedali Baru No 88',
                'provinsi' => 'Jawa Timur',
                'kabupaten_kota' => 'Kabupaten Malang',
                'kecamatan' => 'Lawang',
            ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.sppg.index'))
            ->post(route('admin.sppg.updateStatus', $this->sppg->id_sppg), [
                'status' => 'nonaktif',
                'review_note' => 'Dokumen tidak valid.',
            ]);

        $response->assertRedirect(route('admin.sppg.index'));

        // Nilai aktif tetap nilai lama
        $this->assertEquals('Yu Ji-Min', $this->kepalaUser->fresh()->nama);
        $this->assertEquals('SPPG Malang 01', $this->sppg->fresh()->nama_sppg);
        $this->assertEquals('081726386138', $this->sppg->fresh()->no_telepon);
        $this->assertEquals('terverifikasi', $this->sppg->fresh()->status_verifikasi);
        $this->assertEquals('aktif', $this->sppg->fresh()->status);
    }
}
