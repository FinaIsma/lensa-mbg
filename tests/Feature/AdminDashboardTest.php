<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    private User $adminUser;

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

        $this->adminUser = User::where('email', 'admin1@gmail.com')->first()
            ?: User::where('role', 'admin')->first()
            ?: new User([
                'id_user' => 1,
                'nama' => 'Nanda',
                'email' => 'admin1@gmail.com',
                'role' => 'admin',
                'status' => 'aktif',
            ]);
    }

    /**
     * Test admin dashboard page can be rendered.
     */
    public function test_admin_dashboard_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard & Analytics');
        $response->assertSee('Tren Pendaftaran SPPG');
        $response->assertSee('Distribusi Status SPPG');
        $response->assertSee('Preview Fitur Utama');
        $response->assertSee('SPPG Management');
        $response->assertSee('Audit Trail');
        $response->assertSee('Lihat Selengkapnya');
        $response->assertDontSee('Correction Request');
    }

    /**
     * Test sppg management page can be rendered.
     */
    public function test_sppg_management_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.sppg.index'));

        $response->assertStatus(200);
        $response->assertSee('SPPG Management');
    }

    /**
     * Test user management route redirects to sppg page.
     */
    public function test_user_management_page_redirects_to_sppg(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.users.index'));

        $response->assertRedirect('/admin/sppg');
    }

    /**
     * Test audit trail page can be rendered.
     */
    public function test_audit_trail_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.audit'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
    }
}
