<?php

namespace Tests\Feature\Staff;

use App\Models\AuditTrail;
use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Menu;
use App\Models\ProcessingStep;
use App\Models\ProductionBatch;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProductionBatchTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

    private Staff $staff;

    private Supplier $supplier;

    private Material $material;

    private Menu $menu;

    private Recipe $recipe;

    private RecipeItem $recipeItem;

    private int $seq = 0;

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
            'nama' => 'Kepala Produksi '.uniqid(),
            'email' => 'kepala_prod_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Produksi '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Dapur Produksi MBG',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kabupaten Malang',
            'kecamatan' => 'Lawang',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Dapur '.uniqid(),
            'email' => 'staff_prod_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);

        $this->supplier = Supplier::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Supplier Beras Makmur '.uniqid(),
            'kontak' => '0811223344',
            'alamat' => 'Pasar Induk',
            'status' => 'active',
        ]);

        $this->material = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'BRS_'.uniqid(),
            'nama' => 'Beras Organik Pulen',
            'kategori' => 'makanan_pokok',
            'satuan' => 'kg',
            'min_stock' => 20,
            'is_active' => true,
        ]);

        $this->menu = Menu::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Nasi Ayam Sayur '.uniqid(),
            'kategori' => 'makanan_siap_santap',
            'deskripsi' => 'Menu bergizi seimbang',
            'is_active' => true,
        ]);

        $this->recipe = Recipe::create([
            'menu_id' => $this->menu->id,
            'ukuran_porsi' => 'besar',
            'version' => 1,
            'status' => 'active',
        ]);

        $this->recipeItem = RecipeItem::create([
            'recipe_id' => $this->recipe->id,
            'material_id' => $this->material->id,
            'qty_per_porsi' => 0.05, // 0.05 kg per porsi
            'satuan' => 'kg',
        ]);
    }

    /**
     * Helper membuat MaterialLot lengkap dengan purchase dan receiving
     */
    private function createLot(float $qty, int $daysToExpiry, float $cost = 12000): MaterialLot
    {
        $this->seq++;
        $unique = uniqid().$this->seq;

        $purchase = Purchase::create([
            'id_sppg' => $this->sppg->id_sppg,
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->staff->id,
            'purchase_no' => 'PO-TEST-'.$unique,
            'purchase_date' => Carbon::today(),
            'status' => 'received',
            'is_locked' => true,
        ]);

        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'material_id' => $this->material->id,
            'qty_ordered' => $qty,
            'unit_price' => $cost,
        ]);

        $receiving = Receiving::create([
            'id_sppg' => $this->sppg->id_sppg,
            'purchase_id' => $purchase->id,
            'received_by' => $this->staff->id,
            'receiving_no' => 'RCV-TEST-'.$unique,
            'received_at' => Carbon::now(),
            'status' => 'confirmed',
            'is_locked' => true,
            'confirmed_at' => Carbon::now(),
        ]);

        $receivingItem = ReceivingItem::create([
            'receiving_id' => $receiving->id,
            'purchase_item_id' => $purchaseItem->id,
            'material_id' => $this->material->id,
            'qty_received' => $qty,
            'lot_number_input' => 'LOT-'.$unique,
            'expiry_date_input' => Carbon::today()->addDays($daysToExpiry),
            'kondisi_dilaporkan' => 'Baik',
        ]);

        return MaterialLot::create([
            'receiving_item_id' => $receivingItem->id,
            'material_id' => $this->material->id,
            'supplier_id' => $this->supplier->id,
            'id_sppg' => $this->sppg->id_sppg,
            'lot_number' => 'LOT-'.$unique,
            'barcode' => 'LOT-BAR-'.$unique,
            'received_date' => Carbon::today(),
            'expiry_date' => Carbon::today()->addDays($daysToExpiry),
            'initial_qty' => $qty,
            'current_qty' => $qty,
            'unit_cost' => $cost,
            'status' => 'available',
            'is_locked' => false,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('staff.batches.index'))->assertRedirect(route('login'));
        $this->get(route('staff.pengolahan.index'))->assertRedirect(route('login'));
    }

    public function test_staff_can_view_batch_index_and_stats(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.batches.index'));

        $response->assertStatus(200);
        $response->assertSee('Batch Produksi');
        $response->assertSee('Buat Batch Baru');
        $response->assertSee('Total Batch');
        $response->assertSee('Batch Draft');
    }

    public function test_recipe_materials_api_calculates_requirements_and_sorts_lots_by_fefo(): void
    {
        // Lot 1: Expire dalam 10 hari
        $lot1 = $this->createLot(50, 10, 12000);

        // Lot 2: Expire dalam 3 hari (lebih awal, FEFO harus di atas)
        $lot2 = $this->createLot(30, 3, 12500);

        $targetPorsi = 200;
        // Kebutuhan = 0.05 * 200 = 10 kg
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.batches.recipe-materials', [
                'menu_id' => $this->menu->id,
                'ukuran_porsi' => 'besar',
                'target_porsi' => $targetPorsi,
                'production_date' => date('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertCount(1, $data['blocks']);

        $block = $data['blocks'][0];
        $this->assertEquals(10.0, (float) $block['kebutuhan']);
        $this->assertCount(2, $block['lots']);

        // Assert FEFO: lot2 (exp 3 hari) harus urutan pertama sebelum lot1 (exp 10 hari)
        $this->assertEquals($lot2->id, $block['lots'][0]['id']);
        $this->assertEquals($lot1->id, $block['lots'][1]['id']);
    }

    public function test_staff_can_create_draft_batch(): void
    {
        $lot = $this->createLot(50, 14, 12000);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.batches.store'), [
                'menu_id' => $this->menu->id,
                'ukuran_porsi' => 'besar',
                'production_date' => date('Y-m-d'),
                'target_porsi' => 100,
                'action' => 'draft',
                'usages' => [
                    [
                        'material_lot_id' => $lot->id,
                        'qty_used' => 5.0,
                    ],
                ],
            ]);

        $batch = ProductionBatch::where('menu_id', $this->menu->id)
            ->where('id_sppg', $this->sppg->id_sppg)
            ->latest('id')
            ->first();

        $this->assertNotNull($batch);
        $this->assertEquals('draft', $batch->status);
        $this->assertFalse($batch->is_locked);
        $this->assertEquals(100, $batch->target_porsi);

        // Stok lot belum terpotong pada draft
        $this->assertEquals(50, (float) $lot->fresh()->current_qty);

        // Audit trail dibuat
        $this->assertTrue(AuditTrail::where('auditable_type', 'production_batches')
            ->where('auditable_id', $batch->id)
            ->where('action', 'create')
            ->exists());
    }

    public function test_confirmation_is_blocked_if_recipe_material_is_unfulfilled(): void
    {
        $lot = $this->createLot(50, 14, 12000);

        // Target 100 porsi butuh 0.05 * 100 = 5.0 kg
        // Jika kita hanya mengalokasikan 2.0 kg, konfirmasi harus diblokir!
        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.batches.store'), [
                'menu_id' => $this->menu->id,
                'ukuran_porsi' => 'besar',
                'production_date' => date('Y-m-d'),
                'target_porsi' => 100,
                'action' => 'confirm',
                'usages' => [
                    [
                        'material_lot_id' => $lot->id,
                        'qty_used' => 2.0, // Kurang 3.0 kg!
                    ],
                ],
            ]);

        $response->assertSessionHas('error');
        $error = session('error');
        $this->assertStringContainsString('masih kurang', $error);
    }

    public function test_staff_can_confirm_batch_and_stock_is_deducted(): void
    {
        $lot = $this->createLot(20, 14, 15000);

        // Target 100 butuh 5.0 kg. Kita alokasikan tepat 5.0 kg.
        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.batches.store'), [
                'menu_id' => $this->menu->id,
                'ukuran_porsi' => 'besar',
                'production_date' => date('Y-m-d'),
                'target_porsi' => 100,
                'action' => 'confirm',
                'usages' => [
                    [
                        'material_lot_id' => $lot->id,
                        'qty_used' => 5.0,
                    ],
                ],
            ]);

        $batch = ProductionBatch::where('menu_id', $this->menu->id)
            ->where('id_sppg', $this->sppg->id_sppg)
            ->latest('id')
            ->first();

        $this->assertNotNull($batch);
        $this->assertEquals('confirmed', $batch->status);
        $this->assertTrue($batch->is_locked);
        // Total cost = 5.0 * 15,000 = 75,000
        $this->assertEquals(75000.0, (float) $batch->total_cost);

        // Stok lot terpotong dari 20 menjadi 15
        $this->assertEquals(15.0, (float) $lot->fresh()->current_qty);

        // Mutasi stok PRODUCTION_OUT tercatat
        $movement = StockMovement::where('material_lot_id', $lot->id)
            ->where('movement_type', 'PRODUCTION_OUT')
            ->where('direction', 'OUT')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(5.0, (float) $movement->qty);

        // Audit trail confirm tercatat
        $this->assertTrue(AuditTrail::where('auditable_type', 'production_batches')
            ->where('auditable_id', $batch->id)
            ->where('action', 'confirm')
            ->exists());
    }

    public function test_adding_first_step_switches_batch_from_confirmed_to_processing(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => ProductionBatch::generateBatchCode($this->sppg->id_sppg, date('Y-m-d')),
            'production_date' => date('Y-m-d'),
            'target_porsi' => 150,
            'total_cost' => 100000,
            'status' => 'confirmed',
            'is_locked' => true,
        ]);

        $this->assertEquals('confirmed', $batch->status);

        // Tambah tahap pertama
        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengolahan.step.store'), [
                'production_batch_id' => $batch->id,
                'step_name' => 'Persiapan bahan & Pencucian',
                'started_at' => Carbon::now()->format('Y-m-d\TH:i'),
                'note' => 'Bahan ditimbang dan dibersihkan',
            ]);

        $response->assertSessionHas('success');

        // Assert batch status berganti otomatis menjadi 'processing'
        $batch->refresh();
        $this->assertEquals('processing', $batch->status);

        $step = ProcessingStep::where('production_batch_id', $batch->id)->first();
        $this->assertNotNull($step);
        $this->assertEquals('Persiapan bahan & Pencucian', $step->step_name);
        $this->assertEquals('in_progress', $step->status);
    }

    public function test_staff_can_complete_processing_step(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => ProductionBatch::generateBatchCode($this->sppg->id_sppg, date('Y-m-d')),
            'production_date' => date('Y-m-d'),
            'target_porsi' => 150,
            'status' => 'processing',
            'is_locked' => true,
        ]);

        $step = ProcessingStep::create([
            'production_batch_id' => $batch->id,
            'recorded_by' => $this->staff->id,
            'step_name' => 'Pemasakan',
            'started_at' => Carbon::now()->subHours(2),
            'status' => 'in_progress',
        ]);

        $endedAt = Carbon::now();

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.pengolahan.step.complete', $step->id), [
                'ended_at' => $endedAt->format('Y-m-d\TH:i'),
                'note' => 'Pemasakan matang merata',
            ]);

        $response->assertSessionHas('success');

        $step->refresh();
        $this->assertEquals('done', $step->status);
        $this->assertNotNull($step->ended_at);
        $this->assertStringContainsString('Pemasakan matang merata', $step->note);
    }

    public function test_staff_can_cancel_draft_batch_with_reason(): void
    {
        $batch = ProductionBatch::create([
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'batch_code' => ProductionBatch::generateBatchCode($this->sppg->id_sppg, date('Y-m-d')),
            'production_date' => date('Y-m-d'),
            'target_porsi' => 120,
            'status' => 'draft',
            'is_locked' => false,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.batches.cancel', $batch->id), [
                'reason' => 'Perubahan menu sekolah mendadak',
            ]);

        $response->assertRedirect(route('staff.batches.index'));

        $batch->refresh();
        $this->assertEquals('cancelled', $batch->status);

        $this->assertTrue(AuditTrail::where('auditable_type', 'production_batches')
            ->where('auditable_id', $batch->id)
            ->where('action', 'cancel')
            ->exists());
    }
}
