<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin dashboard page can be rendered.
     */
    public function test_admin_dashboard_can_be_rendered(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard & Analytics');
        $response->assertSee('Tren Pendaftaran SPPG');
        $response->assertSee('Status Pengajuan Koreksi');
        $response->assertSee('Preview Fitur Utama');
        $response->assertSee('User Management');
        $response->assertSee('Correction Request');
        $response->assertSee('Audit Trail');
        $response->assertSee('Lihat Selengkapnya');
    }

    /**
     * Test user management page can be rendered.
     */
    public function test_user_management_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertSee('User Management');
    }

    /**
     * Test correction requests page can be rendered.
     */
    public function test_correction_requests_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.correction-requests.index'));

        $response->assertStatus(200);
        $response->assertSee('Correction Request');
        $response->assertSee('COR-20261012-001');
    }

    /**
     * Test audit trail page can be rendered.
     */
    public function test_audit_trail_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.audit-trail.index'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
        $response->assertSee('confirm');
    }
}
