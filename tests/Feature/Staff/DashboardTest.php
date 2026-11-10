<?php

namespace Tests\Feature\Staff;

use App\Models\DistributionDestination;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DashboardTest extends TestCase
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
            'nama' => 'Kepala Dashboard '.uniqid(),
            'email' => 'kepala_dash_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Dashboard '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Dapur MBG No. 1',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Dashboard '.uniqid(),
            'email' => 'staff_dash_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);
    }

    public function test_staff_dashboard_can_be_rendered_successfully(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('staff.dashboard');
        $response->assertSee('Selamat datang');
        $response->assertSee($this->staff->nama);
        $response->assertSee($this->sppg->nama_sppg);
    }

    public function test_staff_dashboard_displays_correct_stat_cards(): void
    {
        $menu = Menu::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Menu Dashboard '.uniqid(),
            'kategori' => 'makanan_siap_santap',
            'deskripsi' => 'Menu pengujian dashboard',
            'is_active' => true,
        ]);

        Recipe::create([
            'menu_id' => $menu->id,
            'ukuran_porsi' => 'besar',
            'version' => 1,
            'status' => 'active',
        ]);

        $dest = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'SDN 1 Uji Dashboard',
            'tipe' => 'sekolah',
            'alamat' => 'Jl. Pendidikan No. 5',
            'jumlah_penerima' => 250,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Porsi Siap / Target');
        $response->assertSee('Porsi Terdistribusi');
        $response->assertSee('Tugas Pending');
        $response->assertSee('Bahan Kritis Menipis');
    }

    public function test_staff_dashboard_displays_target_distribution_progress(): void
    {
        $destSekolah = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'SMPN 1 Uji',
            'tipe' => 'sekolah',
            'alamat' => 'Jl. Pelajar No. 1',
            'jumlah_penerima' => 300,
        ]);

        $destPosyandu = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Posyandu Melati Uji',
            'tipe' => 'posyandu',
            'alamat' => 'Jl. Sehat No. 2',
            'jumlah_penerima' => 100,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Capaian Distribusi per Sasaran');
        $response->assertSee('Sekolah (SD / SMP / SMA)');
        $response->assertSee('Posyandu / Balita');
        $response->assertSee('Sasaran Khusus / Lainnya');
    }

    public function test_staff_dashboard_displays_live_pipeline_and_operational_cards(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Monitoring Alur Operasional Dapur Hari Ini');
        $response->assertSee('Pembelian PO');
        $response->assertSee('Penerimaan');
        $response->assertSee('Pemasakan');
        $response->assertSee('Distribusi');
        $response->assertSee('Antrean Pengiriman Terbaru');
        $response->assertSee('Peringatan Stok');
    }
}
