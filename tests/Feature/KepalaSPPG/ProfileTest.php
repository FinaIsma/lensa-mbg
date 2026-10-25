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

        $this->assertEquals('Yu Ji-Min Updated', $this->kepalaUser->fresh()->nama);
        $this->assertEquals('SPPG Malang 01 Updated', $this->sppg->fresh()->nama_sppg);
        $this->assertEquals('perubahan', $this->sppg->fresh()->status_verifikasi);
    }
}
