<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Sppg;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_sppg_successfully()
    {
        Storage::fake('public');

        config(['services.supabase.key' => 'mock-supabase-key']);

        Http::fake([
            '*/storage/v1/object/register/*' => Http::response(['Key' => 'register/mock.jpg'], 200),
        ]);

        $email = 'test_' . uniqid() . '@example.com';
        $response = $this->post('/register', [
            'nama_lengkap' => 'Rafif Farrelsyah Fawwazka',
            'email' => $email,
            'no_telepon' => '0899755665',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nama_sppg' => 'SPPG Lawang',
            'alamat_sppg' => 'Jalan Bedali Lawang',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'foto_ktp' => UploadedFile::fake()->image('ktp.jpg', 800, 600)->size(500),
            'foto_kantor_sppg' => UploadedFile::fake()->image('kantor.jpg', 800, 600)->size(500),
            'foto_surat_resmi' => UploadedFile::fake()->create('surat.pdf', 500, 'application/pdf'),
            'syarat_ketentuan' => '1',
        ]);

        $response->assertRedirect(route('login', ['role' => 'admin_sppg']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => $email,
            'role' => 'admin_sppg',
        ]);

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('sppg', [
            'id_user' => $user->id_user,
            'nama_sppg' => 'SPPG Lawang',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'menunggu_verifikasi',
        ]);

        $sppg = Sppg::where('id_user', $user->id_user)->first();
        $this->assertNotNull($sppg);
        $this->assertStringContainsString('supabase.co/storage/v1/object/public/register/ktp/', $sppg->foto_ktp);
        $this->assertStringContainsString('supabase.co/storage/v1/object/public/register/kantor/', $sppg->foto_kantor_sppg);
        $this->assertStringContainsString('supabase.co/storage/v1/object/public/register/surat/', $sppg->foto_surat_resmi);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        User::create([
            'nama' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_sppg',
        ]);

        $response = $this->post('/register', [
            'nama_lengkap' => 'Another User',
            'email' => 'existing@example.com',
            'no_telepon' => '0899755665',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nama_sppg' => 'SPPG Malang',
            'alamat_sppg' => 'Jalan Malang',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kota Malang',
            'kecamatan' => 'Klojen',
            'foto_ktp' => UploadedFile::fake()->image('ktp.jpg'),
            'foto_kantor_sppg' => UploadedFile::fake()->image('kantor.jpg'),
            'foto_surat_resmi' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'syarat_ketentuan' => '1',
        ]);

        $response->assertSessionHasErrors(['email' => 'Email sudah terdaftar dalam sistem.']);
    }

    public function test_check_email_api_endpoint()
    {
        User::create([
            'nama' => 'Existing User',
            'email' => 'used@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_sppg',
        ]);

        $resExists = $this->getJson('/api/check-email?email=used@example.com');
        $resExists->assertOk();
        $resExists->assertJson([
            'available' => false,
            'message' => 'Email sudah terdaftar dalam sistem.',
        ]);

        $resAvailable = $this->getJson('/api/check-email?email=available_new@example.com');
        $resAvailable->assertOk();
        $resAvailable->assertJson([
            'available' => true,
        ]);
    }

    public function test_ajax_register_with_duplicate_email_returns_json_422()
    {
        User::create([
            'nama' => 'Existing User',
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin_sppg',
        ]);

        $response = $this->postJson('/register', [
            'nama_lengkap' => 'Another User',
            'email' => 'existing@example.com',
            'no_telepon' => '0899755665',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'nama_sppg' => 'SPPG Malang',
            'alamat_sppg' => 'Jalan Malang',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kota Malang',
            'kecamatan' => 'Klojen',
            'foto_ktp' => UploadedFile::fake()->image('ktp.jpg'),
            'foto_kantor_sppg' => UploadedFile::fake()->image('kantor.jpg'),
            'foto_surat_resmi' => UploadedFile::fake()->create('surat.pdf', 100, 'application/pdf'),
            'syarat_ketentuan' => '1',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $response->assertJsonFragment([
            'email' => ['Email sudah terdaftar dalam sistem.']
        ]);
    }
}

