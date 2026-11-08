<?php

namespace Tests\Feature\Staff;

use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Menu;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WasteSurplusRecord;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LimbahTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

    private Staff $staff;

    private Menu $menu;

    private Recipe $recipe;

    private Material $material;

    private Supplier $supplier;

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
            'nama' => 'Kepala SPPG '.uniqid(),
            'email' => 'kepala_limbah_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Limbah '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Dapur Limbah MBG',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Limbah '.uniqid(),
            'email' => 'staff_limbah_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);

        $this->menu = Menu::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Menu Limbah '.uniqid(),
            'kategori' => 'sarapan',
            'deskripsi' => 'Menu uji limbah',
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

        $this->material = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Beras Organik '.uniqid(),
            'satuan' => 'kg',
            'kategori' => 'karbohidrat',
        ]);

        $this->supplier = Supplier::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Pemasok Limbah '.uniqid(),
            'no_telepon' => '0811223344',
            'alamat' => 'Malang',
            'status' => 'active',
        ]);
    }

    public function test_staff_can_view_limbah_index(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.limbah.index'));

        $response->assertStatus(200);
        $response->assertSee('Pencatatan Limbah');
        $response->assertSee('Susut Pengolahan');
        $response->assertSee('Makanan Sisa');
        $response->assertSee('Surplus Porsi');
        $response->assertSee('Bahan Dibuang');
    }

    public function test_staff_can_store_production_loss(): void
    {
        Storage::fake('public');

        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'PB-'.strtoupper(uniqid()),
            'production_date' => Carbon::today(),
            'target_porsi' => 500,
            'actual_porsi' => 480,
            'status' => 'ready',
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.limbah.store'), [
                'record_type' => 'PRODUCTION_LOSS',
                'production_batch_id' => $batch->id,
                'qty' => 20,
                'reason' => 'Kerak nasi tebal di dasar panci',
            ]);

        $response->assertRedirect(route('staff.limbah.index'));

        $this->assertDatabaseHas('waste_surplus_records', [
            'id_sppg' => $this->sppg->id_sppg,
            'production_batch_id' => $batch->id,
            'record_type' => 'PRODUCTION_LOSS',
            'qty' => 20,
            'status' => 'active',
        ]);
    }

    public function test_staff_can_store_material_disposal_and_deduct_lot_qty(): void
    {
        $lot = MaterialLot::create([
            'material_id' => $this->material->id,
            'supplier_id' => $this->supplier->id,
            'id_sppg' => $this->sppg->id_sppg,
            'lot_number' => 'LOT-'.uniqid(),
            'barcode' => 'BAR-'.uniqid(),
            'received_date' => now()->subDays(5),
            'expiry_date' => now()->addDays(20),
            'initial_qty' => 50,
            'current_qty' => 50,
            'unit_cost' => 12000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.limbah.store'), [
                'record_type' => 'MATERIAL_DISPOSAL',
                'material_lot_id' => $lot->id,
                'qty' => 15.5,
                'reason' => 'Kemasan karung sobek dan lembap terkontaminasi',
            ]);

        $response->assertRedirect(route('staff.limbah.index'));

        $this->assertDatabaseHas('waste_surplus_records', [
            'id_sppg' => $this->sppg->id_sppg,
            'material_lot_id' => $lot->id,
            'record_type' => 'MATERIAL_DISPOSAL',
            'qty' => 15.5,
            'status' => 'active',
        ]);

        // Verifikasi current_qty terpotong: 50 - 15.5 = 34.5
        $lot->refresh();
        $this->assertEquals(34.5, (float) $lot->current_qty);

        // Verifikasi stock_movements tercatat
        $this->assertDatabaseHas('stock_movements', [
            'material_lot_id' => $lot->id,
            'movement_type' => 'DISPOSAL_OUT',
            'direction' => 'OUT',
            'qty' => 15.5,
        ]);
    }

    public function test_staff_can_ajukan_koreksi(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => 'PB-'.strtoupper(uniqid()),
            'production_date' => Carbon::today(),
            'target_porsi' => 300,
            'actual_porsi' => 300,
            'status' => 'ready',
        ]);

        $record = WasteSurplusRecord::create([
            'id_sppg' => $this->sppg->id_sppg,
            'recorded_by' => $this->staff->id,
            'production_batch_id' => $batch->id,
            'record_type' => 'SURPLUS',
            'qty' => 10,
            'reason' => 'Salah input',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.limbah.koreksi'), [
                'waste_record_id' => $record->id,
                'reason' => 'Salah pencatatan surplus yang seharusnya tidak ada',
            ]);

        $response->assertRedirect(route('staff.limbah.index'));

        $this->assertDatabaseHas('correction_requests', [
            'id_sppg' => $this->sppg->id_sppg,
            'target_table' => 'waste_surplus_records',
            'target_id' => $record->id,
            'field_name' => 'status',
            'new_value' => 'voided',
            'status' => 'pending',
        ]);
    }
}

