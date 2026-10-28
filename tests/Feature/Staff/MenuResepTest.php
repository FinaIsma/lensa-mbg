<?php

namespace Tests\Feature\Staff;

use App\Models\AuditTrail;
use App\Models\Material;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MenuResepTest extends TestCase
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
            'nama' => 'Test Kepala SPPG ' . uniqid(),
            'email' => 'kepala_' . uniqid() . '@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Test ' . uniqid(),
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
            'email' => 'staff_' . uniqid() . '@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('staff.menu-resep.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_menu_resep_page(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.menu-resep.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Menu dan Resep');
        $response->assertSee('Data Menu');
        $response->assertSee('Data Resep');
        $response->assertSee('Total Menu');
        $response->assertSee('Total Resep');
    }

    public function test_recipe_bahan_list_displays_comma_separated(): void
    {
        $menu = Menu::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Nasi Tempe Orek Sayur Bayam',
            'kategori' => 'Makanan Berat',
            'is_active' => true,
        ]);

        $mat1 = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'BRS_' . uniqid(),
            'nama' => 'Beras putih',
            'satuan' => 'kg',
            'is_active' => true,
        ]);

        $mat2 = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'TMP_' . uniqid(),
            'nama' => 'Tempe',
            'satuan' => 'kg',
            'is_active' => true,
        ]);

        $mat3 = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'BYM_' . uniqid(),
            'nama' => 'Sayur Bayam',
            'satuan' => 'kg',
            'is_active' => true,
        ]);

        $recipe = Recipe::create([
            'menu_id' => $menu->id,
            'ukuran_porsi' => 'kecil',
            'version' => 1,
            'status' => 'active',
        ]);

        RecipeItem::create(['recipe_id' => $recipe->id, 'material_id' => $mat1->id, 'qty_per_porsi' => 0.09, 'satuan' => 'kg']);
        RecipeItem::create(['recipe_id' => $recipe->id, 'material_id' => $mat2->id, 'qty_per_porsi' => 0.05, 'satuan' => 'kg']);
        RecipeItem::create(['recipe_id' => $recipe->id, 'material_id' => $mat3->id, 'qty_per_porsi' => 0.04, 'satuan' => 'kg']);

        $recipe->load('items.material');

        $this->assertEquals('Beras putih, Tempe, Sayur Bayam', $recipe->bahan_list);
    }
}
