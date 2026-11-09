<?php

namespace Tests\Feature\Staff;

use App\Models\Distribution;
use App\Models\DistributionDestination;
use App\Models\Menu;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PengirimanTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

    private Staff $staff;

    private Menu $menu;

    private Recipe $recipe;

    private DistributionDestination $destination;

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
            'nama' => 'Kepala Pengiriman '.uniqid(),
            'email' => 'kepala_krm_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Pengiriman '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Dapur Pengiriman MBG',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Pengiriman '.uniqid(),
            'email' => 'staff_krm_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);

        $this->menu = Menu::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Menu Pengiriman '.uniqid(),
            'kategori' => 'makanan_siap_santap',
            'deskripsi' => 'Menu uji pengiriman',
            'is_active' => true,
        ]);

        $this->recipe = Recipe::create([
            'menu_id' => $this->menu->id,
            'id_sppg' => $this->sppg->id_sppg,
            'versi' => 1,
            'ukuran_porsi' => 'besar',
            'standar_porsi' => '1 porsi lengkap',
            'is_active' => true,
        ]);

        $this->destination = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'SDN 1 Uji Kirim '.uniqid(),
            'tipe' => 'sekolah',
            'alamat' => 'Jl. Pengujian No. 10',
            'jumlah_penerima' => 200,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('staff.pengiriman.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_pengiriman_index(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.pengiriman.index'));

        $response->assertStatus(200);
        $response->assertViewIs('staff.pengiriman.index');
        $response->assertSee('Total Pengiriman');
        $response->assertSee('Dalam Proses / Kirim');
        $response->assertSee('Porsi Sukses Diterima');
        $response->assertSee('Semua Tipe');
        $response->assertSee('Semua Status');
        $response->assertSee('name="date"', false);
    }

    public function test_staff_can_filter_pengiriman_by_tipe(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-FILT-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 200,
            'actual_porsi' => 200,
            'total_cost' => 1000000,
            'status' => 'ready',
        ]);

        $posyandu = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Posyandu Filter '.uniqid(),
            'tipe' => 'posyandu',
            'alamat' => 'Jl. Posyandu Uji',
            'jumlah_penerima' => 50,
        ]);

        Distribution::create([
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id, // Sekolah
            'recorded_by' => $this->staff->id,
            'portions_sent' => 50,
            'status' => 'sent',
            'distributed_at' => now(),
        ]);

        Distribution::create([
            'production_batch_id' => $batch->id,
            'destination_id' => $posyandu->id, // Posyandu
            'recorded_by' => $this->staff->id,
            'portions_sent' => 30,
            'status' => 'planned',
            'distributed_at' => now(),
        ]);

        $responseSekolah = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.pengiriman.index', ['tipe' => 'sekolah']));
        $responseSekolah->assertStatus(200);
        $distsSekolah = $responseSekolah->viewData('distributions');
        $this->assertTrue($distsSekolah->contains('destination_id', $this->destination->id));
        $this->assertFalse($distsSekolah->contains('destination_id', $posyandu->id));

        $responsePosyandu = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.pengiriman.index', ['tipe' => 'posyandu']));
        $responsePosyandu->assertStatus(200);
        $distsPosyandu = $responsePosyandu->viewData('distributions');
        $this->assertTrue($distsPosyandu->contains('destination_id', $posyandu->id));
        $this->assertFalse($distsPosyandu->contains('destination_id', $this->destination->id));
    }

    public function test_staff_can_fetch_batch_info_json(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-TEST-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 300,
            'actual_porsi' => 290,
            'total_cost' => 1500000,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->getJson(route('staff.pengiriman.batch-info', $batch->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $batch->id,
                'target_porsi' => 300,
                'actual_porsi' => 290,
                'sisa_porsi' => 290,
            ],
        ]);
    }

    public function test_staff_can_store_valid_planned_distribution(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-KRM-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 250,
            'actual_porsi' => 250,
            'total_cost' => 1250000,
            'status' => 'ready',
        ]);

        $payload = [
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'portions_sent' => 100,
            'distributed_at' => Carbon::now()->format('Y-m-d\TH:i'),
            'status' => 'planned',
        ];

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengiriman.store'), $payload);

        $response->assertRedirect(route('staff.pengiriman.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('distributions', [
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'portions_sent' => 100,
            'status' => 'planned',
        ]);
    }

    public function test_store_fails_when_portions_exceed_remaining_batch_quota(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-LIMIT-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 100,
            'actual_porsi' => 100,
            'total_cost' => 500000,
            'status' => 'ready',
        ]);

        $payload = [
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'portions_sent' => 150, // Melebihi actual_porsi (100)
            'distributed_at' => Carbon::now()->format('Y-m-d\TH:i'),
            'status' => 'planned',
        ];

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengiriman.store'), $payload);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('distributions', [
            'production_batch_id' => $batch->id,
            'portions_sent' => 150,
        ]);
    }

    public function test_staff_can_mark_shipment_as_sent_and_updates_batch_status(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-SENT-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 200,
            'actual_porsi' => 200,
            'total_cost' => 1000000,
            'status' => 'ready',
        ]);

        $dist = Distribution::create([
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'recorded_by' => $this->staff->id,
            'portions_sent' => 100,
            'status' => 'planned',
            'distributed_at' => now(),
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengiriman.mark-sent', $dist->id));

        $response->assertRedirect(route('staff.pengiriman.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('sent', $dist->fresh()->status);
        $this->assertEquals('distributed', $batch->fresh()->status);
    }

    public function test_staff_can_mark_shipment_as_received_and_completes_batch(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-RECV-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 100,
            'actual_porsi' => 100,
            'total_cost' => 500000,
            'status' => 'distributed',
        ]);

        $dist = Distribution::create([
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'recorded_by' => $this->staff->id,
            'portions_sent' => 100,
            'status' => 'sent',
            'distributed_at' => now(),
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengiriman.mark-received', $dist->id));

        $response->assertRedirect(route('staff.pengiriman.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('received', $dist->fresh()->status);
        $this->assertEquals('completed', $batch->fresh()->status);
    }

    public function test_staff_can_cancel_planned_shipment(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-CANCEL-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 100,
            'actual_porsi' => 100,
            'total_cost' => 500000,
            'status' => 'ready',
        ]);

        $dist = Distribution::create([
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'recorded_by' => $this->staff->id,
            'portions_sent' => 50,
            'status' => 'planned',
            'distributed_at' => now(),
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengiriman.cancel', $dist->id), [
                'cancellation_reason' => 'Sekolah libur mendadak karena acara dinas',
            ]);

        $response->assertRedirect(route('staff.pengiriman.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('cancelled', $dist->fresh()->status);
    }

    public function test_staff_can_show_shipment_detail_json(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'BATCH-DETAIL-'.uniqid(),
            'production_date' => Carbon::today(),
            'target_porsi' => 150,
            'actual_porsi' => 150,
            'total_cost' => 750000,
            'status' => 'ready',
        ]);

        $dist = Distribution::create([
            'production_batch_id' => $batch->id,
            'destination_id' => $this->destination->id,
            'recorded_by' => $this->staff->id,
            'portions_sent' => 75,
            'status' => 'planned',
            'distributed_at' => now(),
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->getJson(route('staff.pengiriman.show', $dist->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $dist->id,
                'portions_sent' => 75,
                'status' => 'planned',
            ],
        ]);
    }
}
