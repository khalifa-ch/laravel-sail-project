<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->ticket = Ticket::factory()->create();

        Sanctum::actingAs(User::factory()->create()->assignRole('admin'));
    }

    private function assign(User $user)
    {
        return $this->patchJson("/api/tickets/{$this->ticket->id}/assign-agent", [
            'assigned_agent_id' => $user->id,
        ]);
    }

    public function test_admin_can_assign_an_agent(): void
    {
        $agent = User::factory()->create()->assignRole('agent');

        $this->assign($agent)->assertOk();
        $this->assertSame($agent->id, $this->ticket->fresh()->assigned_agent_id);
    }

    public function test_admin_cannot_assign_a_user_who_is_not_an_agent(): void
    {
        $client = User::factory()->create()->assignRole('client');

        $this->assign($client)->assertUnprocessable()->assertJsonValidationErrors('assigned_agent_id');
        $this->assertNull($this->ticket->fresh()->assigned_agent_id);
    }
}
