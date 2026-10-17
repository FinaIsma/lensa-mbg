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
        $response->assertSee('SPPG Management');
        $response->assertSee('Correction Request');
        $response->assertSee('Audit Trail');
        $response->assertSee('Lihat Selengkapnya');
    }

    /**
     * Test sppg management page can be rendered.
     */
    public function test_sppg_management_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.sppg.index'));

        $response->assertStatus(200);
        $response->assertSee('SPPG Management');
    }

    /**
     * Test user management route redirects to sppg page.
     */
    public function test_user_management_page_redirects_to_sppg(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect('/admin/sppg');
    }

    /**
     * Test correction requests page can be rendered.
     */
    public function test_correction_requests_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.correction'));

        $response->assertStatus(200);
        $response->assertSee('Correction Request');
    }

    /**
     * Test audit trail page can be rendered.
     */
    public function test_audit_trail_page_can_be_rendered(): void
    {
        $response = $this->get(route('admin.audit'));

        $response->assertStatus(200);
        $response->assertSee('Audit Trail');
    }
}
