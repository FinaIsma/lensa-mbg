<?php

namespace Tests\Feature\Staff;

use App\Models\Menu;
use App\Models\ProcessingStep;
use App\Models\ProductionBatch;
use App\Models\Recipe;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HasilProduksiTest extends TestCase
{
    use DatabaseTransactions;

    protected $connectionsToTransact = ['pgsql'];

    private User $kepalaUser;

    private Sppg $sppg;

    private Staff $staff;

    private Menu $menu;

    private Recipe $recipe;

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
        Storage::fake('public');

        $this->kepalaUser = User::create([
            'nama' => 'Kepala Hasil '.uniqid(),
            'email' => 'kepala_hsl_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'role' => 'kepala_sppg',
            'status' => 'aktif',
        ]);

        $this->sppg = Sppg::create([
            'id_user' => $this->kepalaUser->id_user,
            'nama_sppg' => 'SPPG Hasil '.uniqid(),
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
            'email' => 'staff_hsl_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);

        $this->menu = Menu::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Menu Uji Fisik '.uniqid(),
            'kategori' => 'makanan_siap_santap',
            'deskripsi' => 'Menu gizi seimbang',
            'is_active' => true,
        ]);

        $this->recipe = Recipe::create([
            'menu_id' => $this->menu->id,
            'ukuran_porsi' => 'besar',
            'version' => 1,
            'is_active' => true,
        ]);
    }

    public function test_halaman_hasil_produksi_dapat_diakses_dan_menampilkan_data(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.hasil.index'));

        $response->assertStatus(200);
        $response->assertSee('Catat Hasil Baru');
        $response->assertSee('Uji Fisik &amp; Kondisi', false);
        $response->assertSee('Ahli Gizi');
        $response->assertSee('Waktu QC &amp; Batas Kritis', false);
    }

    public function test_simpan_hasil_produksi_dengan_waktu_kritis_ahli_gizi_dan_matriks_uji_fisik(): void
    {
        $batch = ProductionBatch::create([
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'batch_code' => 'BATCH-TEST-'.uniqid(),
            'production_date' => now()->toDateString(),
            'target_porsi' => 100,
            'status' => 'processing',
        ]);

        ProcessingStep::create([
            'production_batch_id' => $batch->id,
            'step_name' => 'Pemasakan',
            'step_order' => 1,
            'status' => 'done',
            'recorded_by' => $this->staff->id,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
        ]);

        $checkedAt = now()->format('Y-m-d\TH:i');
        $estimatedDist = now()->addMinutes(30)->format('Y-m-d\TH:i');
        $consumeBy = now()->addHours(4)->format('Y-m-d\TH:i');

        $file = UploadedFile::fake()->image('porsi_siap.jpg');

        $payload = [
            'production_batch_id' => $batch->id,
            'form_type' => 'makanan_siap_santap',
            'portions_ready' => 100,
            'nutritionist_id' => $this->staff->id,
            'nutritionist_name' => 'Fadhilah Ayu, S.Gz',
            'checked_at' => $checkedAt,
            'estimated_distribution_at' => $estimatedDist,
            'consume_by' => $consumeBy,
            'reported_condition' => 'Lolos Uji Fisik: Matang sempurna, aroma segar, higienis siap distribusi',
            'physical_test_status' => 'passed',
            'physical_test_matrix' => [
                'rasa' => ['label' => 'Rasa', 'status' => 'sesuai', 'keterangan' => 'Gurih sesuai standar'],
                'aroma' => ['label' => 'Aroma', 'status' => 'segar', 'keterangan' => 'Segar rempah'],
                'warna' => ['label' => 'Warna', 'status' => 'cerah', 'keterangan' => 'Menarik cerah'],
                'tekstur' => ['label' => 'Tekstur', 'status' => 'matang', 'keterangan' => 'Empuk pas'],
                'suhu' => ['label' => 'Suhu', 'status' => 'hangat', 'keterangan' => 'Hangat ≥60C'],
                'kemasan' => ['label' => 'Kemasan', 'status' => 'rapat', 'keterangan' => 'Tertutup rapat'],
            ],
            'note' => 'Menu siap didistribusikan ke sekolah target.',
            'evidence_file' => $file,
            'is_public' => 1,
        ];

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.hasil.store'), $payload);

        $response->assertRedirect(route('staff.hasil.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pre_distribution_checks', [
            'production_batch_id' => $batch->id,
            'form_type' => 'makanan_siap_santap',
            'portions_ready' => 100,
            'nutritionist_name' => 'Fadhilah Ayu, S.Gz',
            'physical_test_status' => 'passed',
        ]);

        $batch->refresh();
        $this->assertEquals('ready', $batch->status);
        $this->assertEquals(100, $batch->actual_porsi);
        $this->assertNotNull($batch->preDistributionCheck?->consume_by);
        $this->assertNotNull($batch->preDistributionCheck?->estimated_distribution_at);
    }

    public function test_validasi_waktu_kritis_harus_setelah_jam_uji_fisik(): void
    {
        $batch = ProductionBatch::create([
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'batch_code' => 'BATCH-TEST-INVALID-'.uniqid(),
            'production_date' => now()->toDateString(),
            'target_porsi' => 100,
            'status' => 'processing',
        ]);

        ProcessingStep::create([
            'production_batch_id' => $batch->id,
            'step_name' => 'Pemasakan',
            'step_order' => 1,
            'status' => 'done',
            'recorded_by' => $this->staff->id,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
        ]);

        $checkedAt = now()->format('Y-m-d\TH:i');
        // Waktu kritis dibuat sebelum jam uji fisik (tidak valid)
        $invalidConsumeBy = now()->subHour()->format('Y-m-d\TH:i');

        $file = UploadedFile::fake()->image('porsi_siap.jpg');

        $payload = [
            'production_batch_id' => $batch->id,
            'form_type' => 'makanan_siap_santap',
            'portions_ready' => 100,
            'nutritionist_name' => 'Fadhilah Ayu, S.Gz',
            'checked_at' => $checkedAt,
            'consume_by' => $invalidConsumeBy,
            'reported_condition' => 'Baik',
            'evidence_file' => $file,
        ];

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.hasil.store'), $payload);

        $response->assertSessionHasErrors(['consume_by']);
    }

    public function test_simpan_hasil_produksi_dengan_custom_nutritionist_berhasil_dan_id_menjadi_null(): void
    {
        $batch = ProductionBatch::create([
            'id_sppg' => $this->sppg->id_sppg,
            'created_by' => $this->staff->id,
            'menu_id' => $this->menu->id,
            'recipe_id' => $this->recipe->id,
            'batch_code' => 'BATCH-TEST-CUSTOM-NUTRITIONIST-'.uniqid(),
            'production_date' => now()->toDateString(),
            'target_porsi' => 80,
            'status' => 'processing',
        ]);

        ProcessingStep::create([
            'production_batch_id' => $batch->id,
            'step_name' => 'Pemasakan',
            'step_order' => 1,
            'status' => 'done',
            'recorded_by' => $this->staff->id,
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHour(),
        ]);

        $checkedAt = now()->format('Y-m-d\TH:i');
        $consumeBy = now()->addHours(4)->format('Y-m-d\TH:i');
        $file = UploadedFile::fake()->image('porsi_siap.jpg');

        $payload = [
            'production_batch_id' => $batch->id,
            'form_type' => 'makanan_siap_santap',
            'portions_ready' => 80,
            // Simulasi form kirim 'custom' (pilihan ketik manual nama ahli gizi luar)
            'nutritionist_id' => 'custom',
            'nutritionist_name' => 'Dr. Maya Kartika, M.Gz',
            'checked_at' => $checkedAt,
            'consume_by' => $consumeBy,
            'reported_condition' => 'Lolos Uji Fisik: Matang sempurna, rasa gurih khas',
            'evidence_file' => $file,
        ];

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.hasil.store'), $payload);

        $response->assertRedirect(route('staff.hasil.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('pre_distribution_checks', [
            'production_batch_id' => $batch->id,
            'nutritionist_id' => null,
            'nutritionist_name' => 'Dr. Maya Kartika, M.Gz',
        ]);
    }
}
