<?php

namespace Tests\Feature\Staff;

use App\Models\Distribution;
use App\Models\DistributionDestination;
use App\Models\ProductionBatch;
use App\Models\Sppg;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DestinationTest extends TestCase
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
            'nama' => 'Staff Tester '.uniqid(),
            'email' => 'staff_'.uniqid().'@sppg.id',
            'password' => Hash::make('password123'),
            'status' => 'aktif',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('staff.destinations.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_view_tujuan_page_without_error(): void
    {
        $destination = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'SD Negeri 1 Contoh '.uniqid(),
            'tipe' => 'sekolah',
            'alamat' => 'Jl. Pendidikan No. 10',
            'jumlah_penerima' => 250,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.destinations.index'));

        $response->assertStatus(200);
        $response->assertSee('Data Tujuan Distribusi');
        $response->assertSee('Total Tujuan');
        $response->assertSee('Total Target Penerima');
        $response->assertSee('Total Pengiriman');
        $response->assertSee($destination->nama);
        $response->assertSee('Sekolah');
        $response->assertSee('250');
    }

    public function test_staff_can_search_tujuan_and_filter_by_tipe(): void
    {
        $dest1 = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'SD Harapan Bangsa '.uniqid(),
            'tipe' => 'sekolah',
            'alamat' => 'Jl. Bangsa No. 1',
            'jumlah_penerima' => 150,
        ]);

        $dest2 = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Posyandu Mawar Sehat '.uniqid(),
            'tipe' => 'posyandu',
            'alamat' => 'Balai RW 05',
            'jumlah_penerima' => 45,
        ]);

        // Search test
        $responseSearch = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.destinations.index', ['q' => 'Harapan Bangsa']));

        $responseSearch->assertStatus(200);
        $responseSearch->assertSee($dest1->nama);
        $responseSearch->assertDontSee($dest2->nama);

        // Filter tipe test
        $responseFilter = $this->actingAs($this->staff, 'staff')
            ->get(route('staff.destinations.index', ['tipe' => 'posyandu']));

        $responseFilter->assertStatus(200);
        $responseFilter->assertSee($dest2->nama);
        $responseFilter->assertDontSee($dest1->nama);
    }

    public function test_staff_can_create_tujuan_successfully(): void
    {
        $payload = [
            'nama' => 'SMP Negeri 5 Test',
            'tipe' => 'sekolah',
            'alamat' => 'Jl. Pahlawan No. 99',
            'jumlah_penerima' => 380,
        ];

        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.destinations.store'), $payload);

        $response->assertRedirect(route('staff.destinations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('distribution_destinations', [
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'SMP Negeri 5 Test',
            'tipe' => 'sekolah',
            'jumlah_penerima' => 380,
        ]);
    }

    public function test_create_tujuan_validation_fails_on_missing_required_fields(): void
    {
        $response = $this->actingAs($this->staff, 'staff')
            ->post(route('staff.destinations.store'), [
                'nama' => '',
                'tipe' => 'invalid_type',
                'jumlah_penerima' => -10,
            ]);

        $response->assertSessionHasErrors(['nama', 'tipe', 'jumlah_penerima']);
    }

    public function test_staff_can_view_tujuan_detail_json(): void
    {
        $destination = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Posyandu Teratai '.uniqid(),
            'tipe' => 'posyandu',
            'alamat' => 'Jl. Melati RW 02',
            'jumlah_penerima' => 60,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->getJson(route('staff.destinations.show', $destination->id));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'id' => $destination->id,
                'nama' => $destination->nama,
                'tipe' => 'posyandu',
                'tipe_label' => 'Posyandu',
                'jumlah_penerima' => 60,
            ],
        ]);
    }

    public function test_staff_can_update_tujuan(): void
    {
        $destination = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Nama Lama '.uniqid(),
            'tipe' => 'sekolah',
            'alamat' => 'Alamat Lama',
            'jumlah_penerima' => 100,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->put(route('staff.destinations.update', $destination->id), [
                'nama' => 'Nama Baru Diperbarui',
                'tipe' => 'posyandu',
                'alamat' => 'Alamat Baru Lengkap',
                'jumlah_penerima' => 120,
            ]);

        $response->assertRedirect(route('staff.destinations.index'));
        $response->assertSessionHas('success');

        $destination->refresh();
        $this->assertSame('Nama Baru Diperbarui', $destination->nama);
        $this->assertSame('posyandu', $destination->tipe);
        $this->assertSame(120, $destination->jumlah_penerima);
    }

    public function test_staff_can_delete_unused_tujuan(): void
    {
        $destination = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Tujuan Hapus '.uniqid(),
            'tipe' => 'lainnya',
            'alamat' => 'Alamat',
            'jumlah_penerima' => 50,
        ]);

        $response = $this->actingAs($this->staff, 'staff')
            ->delete(route('staff.destinations.destroy', $destination->id));

        $response->assertRedirect(route('staff.destinations.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('distribution_destinations', [
            'id' => $destination->id,
        ]);
    }

    public function test_staff_cannot_delete_tujuan_with_distributions(): void
    {
        $destination = DistributionDestination::create([
            'id_sppg' => $this->sppg->id_sppg,
            'nama' => 'Tujuan Dipakai '.uniqid(),
            'tipe' => 'sekolah',
            'alamat' => 'Alamat',
            'jumlah_penerima' => 200,
        ]);

        // Get an existing production batch or create one
        $batch = ProductionBatch::where('id_sppg', $this->sppg->id_sppg)->first();
        if (! $batch) {
            $batch = ProductionBatch::first();
        }

        if ($batch) {
            Distribution::create([
                'production_batch_id' => $batch->id,
                'destination_id' => $destination->id,
                'recorded_by' => $this->staff->id,
                'portions_sent' => 50,
                'status' => 'received',
                'distributed_at' => now(),
            ]);

            $response = $this->actingAs($this->staff, 'staff')
                ->delete(route('staff.destinations.destroy', $destination->id));

            $response->assertSessionHas('error');
            $this->assertDatabaseHas('distribution_destinations', [
                'id' => $destination->id,
            ]);
        }
    }
}
