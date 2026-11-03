<?php

namespace Tests\Feature;

use App\Models\Material;
use App\Models\MaterialLot;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RiwayatStokTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

    private Staff $staff;

    private Material $material;

    private Supplier $supplier;

    private MaterialLot $lot;

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
            'nama' => 'Kepala SPPG Test '.uniqid(),
            'email' => 'kepala_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Test '.uniqid(),
            'no_telepon' => '081234567890',
            'alamat_sppg' => 'Jl Contoh SPPG No 1',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kota Surabaya',
            'kecamatan' => 'Gubeng',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $this->staff = Staff::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Staff Operasional '.uniqid(),
            'email' => 'staff_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);

        $this->supplier = Supplier::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Supplier Segar '.uniqid(),
            'kontak' => '0811223344',
            'alamat' => 'Pasar Induk',
            'status' => 'active',
        ]);

        $this->material = Material::create([
            'id_sppg' => $this->sppg->id_sppg,
            'kode' => 'AYM_'.rand(100, 999),
            'nama' => 'Daging Ayam Segar',
            'kategori' => 'Protein Hewani',
            'satuan' => 'kg',
            'min_stock' => 10,
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'id_sppg' => $this->sppg->id_sppg,
            'supplier_id' => $this->supplier->id,
            'created_by' => $this->staff->id,
            'purchase_no' => 'PO-'.date('Ymd').'-'.rand(100, 999),
            'purchase_date' => Carbon::today(),
            'status' => 'received',
        ]);

        $purchaseItem = PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'material_id' => $this->material->id,
            'qty_ordered' => 50,
            'unit_price' => 38000,
        ]);

        $receiving = Receiving::create([
            'id_sppg' => $this->sppg->id_sppg,
            'purchase_id' => $purchase->id,
            'received_by' => $this->staff->id,
            'receiving_no' => 'RCV-'.date('Ymd').'-'.rand(100, 999),
            'received_at' => Carbon::now(),
            'status' => 'confirmed',
            'is_locked' => true,
            'confirmed_at' => Carbon::now(),
        ]);

        $receivingItem = ReceivingItem::create([
            'receiving_id' => $receiving->id,
            'purchase_item_id' => $purchaseItem->id,
            'material_id' => $this->material->id,
            'qty_received' => 50,
            'lot_number_input' => 'LOT-SUPP-99',
            'expiry_date_input' => Carbon::today()->addDays(10),
            'kondisi_dilaporkan' => 'Kondisi Segar',
        ]);

        $this->lot = MaterialLot::create([
            'receiving_item_id' => $receivingItem->id,
            'material_id' => $this->material->id,
            'supplier_id' => $this->supplier->id,
            'id_sppg' => $this->sppg->id_sppg,
            'lot_number' => 'LOT-SUPP-99',
            'barcode' => 'LOT-'.rand(100000, 999999),
            'received_date' => Carbon::today(),
            'expiry_date' => Carbon::today()->addDays(10),
            'initial_qty' => 50,
            'current_qty' => 30,
            'unit_cost' => 38000,
            'status' => 'available',
            'is_locked' => true,
        ]);

        // Catat mutasi 1: Masuk 50 kg
        StockMovement::create([
            'material_lot_id' => $this->lot->id,
            'created_by' => $this->staff->id,
            'movement_type' => 'RECEIVE_IN',
            'direction' => 'IN',
            'qty' => 50,
            'reference_type' => 'receiving_items',
            'reference_id' => $receivingItem->id,
            'created_at' => Carbon::now()->subHours(2),
        ]);

        // Catat mutasi 2: Keluar 20 kg
        StockMovement::create([
            'material_lot_id' => $this->lot->id,
            'created_by' => $this->staff->id,
            'movement_type' => 'PRODUCTION_OUT',
            'direction' => 'OUT',
            'qty' => 20,
            'reference_type' => 'material_usages',
            'reference_id' => 9999,
            'created_at' => Carbon::now()->subHour(),
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('staff.riwayat.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_riwayat_index_page(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.riwayat.index'));

        $response->assertStatus(200);
        $response->assertSee('Riwayat Stok Bahan');
        $response->assertSee('Total Masuk');
        $response->assertSee('Total Keluar');
        $response->assertSee('Selisih Mutasi');
        $response->assertSee($this->material->nama);
        $response->assertSee($this->lot->barcode);
    }

    public function test_staff_can_filter_movements_by_direction(): void
    {
        // Filter IN
        $responseIn = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.riwayat.index', ['direction' => 'IN']));

        $responseIn->assertStatus(200);
        $responseIn->assertSee('Penerimaan Bahan');
        $responseIn->assertSee('+50,00');

        // Filter OUT
        $responseOut = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.riwayat.index', ['direction' => 'OUT']));

        $responseOut->assertStatus(200);
        $responseOut->assertSee('Pemakaian Produksi');
        $responseOut->assertSee('-20,00');
    }

    public function test_staff_can_filter_by_lot(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.riwayat.index', [
                'lot_id' => $this->lot->id,
            ]));

        $response->assertStatus(200);
        $response->assertSee($this->lot->barcode);
        $response->assertSee($this->material->nama);
        $response->assertSee('+50,00');
        $response->assertSee('-20,00');
    }

    public function test_multi_tenancy_isolation_prevents_leakage(): void
    {
        // Buat SPPG lain dengan lot dan mutasi
        $otherUser = User::create([
            'nama' => 'Kepala Lain '.uniqid(),
            'email' => 'other_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $otherSppg = Sppg::create([
            'id_user' => $otherUser->id_user,
            'nama_sppg' => 'SPPG Rahasia Lain',
            'no_telepon' => '0899887766',
            'alamat_sppg' => 'Jl Rahasia',
            'provinsi' => 'Jawa Timur',
            'kabupaten_kota' => 'Kota Malang',
            'kecamatan' => 'Klojen',
            'status' => 'aktif',
            'status_verifikasi' => 'terverifikasi',
        ]);

        $otherStaff = Staff::create([
            'id_sppg' => $otherSppg->id_sppg,
            'nama' => 'Staff Lain',
            'email' => 'other_staff_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);

        $otherMaterial = Material::create([
            'id_sppg' => $otherSppg->id_sppg,
            'kode' => 'RHS_'.rand(100, 999),
            'nama' => 'Bahan Rahasia SPPG Lain',
            'kategori' => 'Rahasia',
            'satuan' => 'kg',
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $otherSupplier = Supplier::create([
            'id_sppg' => $otherSppg->id_sppg,
            'nama' => 'Supplier Lain '.uniqid(),
            'kontak' => '0812999999',
            'alamat' => 'Alamat Lain',
            'status' => 'active',
        ]);

        $otherPurchase = Purchase::create([
            'id_sppg' => $otherSppg->id_sppg,
            'supplier_id' => $otherSupplier->id,
            'created_by' => $otherStaff->id,
            'purchase_no' => 'PO-OTHER-'.rand(100, 999),
            'purchase_date' => Carbon::today(),
            'status' => 'received',
        ]);

        $otherPItem = PurchaseItem::create([
            'purchase_id' => $otherPurchase->id,
            'material_id' => $otherMaterial->id,
            'qty_ordered' => 100,
            'unit_price' => 10000,
        ]);

        $otherReceiving = Receiving::create([
            'id_sppg' => $otherSppg->id_sppg,
            'purchase_id' => $otherPurchase->id,
            'received_by' => $otherStaff->id,
            'receiving_no' => 'RCV-OTHER-'.rand(100, 999),
            'received_at' => Carbon::now(),
            'status' => 'confirmed',
            'is_locked' => true,
            'confirmed_at' => Carbon::now(),
        ]);

        $otherRItem = ReceivingItem::create([
            'receiving_id' => $otherReceiving->id,
            'purchase_item_id' => $otherPItem->id,
            'material_id' => $otherMaterial->id,
            'qty_received' => 100,
            'lot_number_input' => 'LOT-OTHER-SECRET',
            'expiry_date_input' => Carbon::today()->addDays(30),
            'kondisi_dilaporkan' => 'Baik',
        ]);

        $otherLot = MaterialLot::create([
            'receiving_item_id' => $otherRItem->id,
            'material_id' => $otherMaterial->id,
            'supplier_id' => $otherSupplier->id,
            'id_sppg' => $otherSppg->id_sppg,
            'lot_number' => 'LOT-OTHER-SECRET',
            'barcode' => 'LOT-SECRET-'.rand(100000, 999999),
            'received_date' => Carbon::today(),
            'expiry_date' => Carbon::today()->addDays(30),
            'initial_qty' => 100,
            'current_qty' => 100,
            'unit_cost' => 10000,
            'status' => 'available',
            'is_locked' => true,
        ]);

        StockMovement::create([
            'material_lot_id' => $otherLot->id,
            'created_by' => $otherStaff->id,
            'movement_type' => 'RECEIVE_IN',
            'direction' => 'IN',
            'qty' => 100,
            'reference_type' => 'receiving_items',
            'reference_id' => $otherRItem->id,
            'created_at' => Carbon::now(),
        ]);

        // Staff awal tidak boleh melihat 'Bahan Rahasia SPPG Lain'
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.riwayat.index'));

        $response->assertStatus(200);
        $response->assertDontSee('Bahan Rahasia SPPG Lain');
        $response->assertDontSee($otherLot->barcode);
    }
}
