<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_access_the_panel(): void
    {
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_agent_can_access_the_panel(): void
    {
        $agent = User::factory()->create()->assignRole('agent');

        $this->actingAs($agent)->get('/admin')->assertOk();
    }

    public function test_client_cannot_access_the_panel(): void
    {
        $client = User::factory()->create()->assignRole('client');

        $this->actingAs($client)->get('/admin')->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
