<?php

namespace Tests\Feature\Staff;

use App\Models\Material;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MaterialTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

    private Staff $staff;

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
            'nama_sppg' => 'SPPG Test '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Contoh SPPG',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Roh Yu-na',
            'email' => 'staff_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('staff.materials.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_bahan_page_without_error(): void
    {
        $material = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'BRS_'.uniqid(),
            'nama' => 'Beras Pandan Wangi',
            'kategori' => 'Karbohidrat',
            'satuan' => 'KG',
            'min_stock' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.materials.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Bahan Baku');
        $response->assertSee('Stok total');
        $response->assertSee('Stok minimum');
        $response->assertSee('Indikator');
        $response->assertSee('Aktif');
        $response->assertSee('Aksi');
        $response->assertSee($material->kode);
        $response->assertSee('Beras Pandan Wangi');
    }

    public function test_staff_can_search_materials_by_kode_and_nama(): void
    {
        $uniqueCode1 = 'KODE1_'.uniqid();
        $uniqueCode2 = 'KODE2_'.uniqid();

        $mat1 = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => $uniqueCode1,
            'nama' => 'Beras Organik Merah',
            'kategori' => 'Karbohidrat',
            'satuan' => 'KG',
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $mat2 = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => $uniqueCode2,
            'nama' => 'Minyak Kelapa Sawit',
            'kategori' => 'Bumbu & minyak',
            'satuan' => 'Liter',
            'min_stock' => 5,
            'is_active' => true,
        ]);

        // Cari berdasarkan kode
        $responseCode = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.materials.index', ['q' => strtolower($uniqueCode1)]));

        $responseCode->assertStatus(200);
        $responseCode->assertSee($uniqueCode1);
        $responseCode->assertSee('Beras Organik Merah');
        $responseCode->assertDontSee($uniqueCode2);

        // Cari berdasarkan nama
        $responseNama = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.materials.index', ['q' => 'Kelapa Sawit']));

        $responseNama->assertStatus(200);
        $responseNama->assertSee($uniqueCode2);
        $responseNama->assertSee('Minyak Kelapa Sawit');
        $responseNama->assertDontSee('Beras Organik Merah');
    }

    public function test_staff_can_view_detail_json(): void
    {
        $material = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'AYM_'.uniqid(),
            'nama' => 'Daging Ayam Fillet',
            'kategori' => 'Protein hewani',
            'satuan' => 'KG',
            'min_stock' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.materials.show', $material->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $material->id,
                'kode' => $material->kode,
                'nama' => 'Daging Ayam Fillet',
                'kategori' => 'Protein hewani',
                'satuan' => 'KG',
                'is_active' => true,
            ],
        ]);
    }
}
