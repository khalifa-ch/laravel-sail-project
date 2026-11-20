<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TicketStatusTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->agent = User::factory()->create()->assignRole('agent');
    }

    private function ticketAssignedToAgent(string $status): Ticket
    {
        return Ticket::factory()->create([
            'status'            => $status,
            'assigned_agent_id' => $this->agent->id,
        ]);
    }

    private function changeStatus(Ticket $ticket, string $status)
    {
        return $this->patchJson("/api/tickets/{$ticket->id}/change-status", ['status' => $status]);
    }

    public function test_agent_can_move_ticket_forward(): void
    {
        $ticket = $this->ticketAssignedToAgent('open');
        Sanctum::actingAs($this->agent);

        $this->changeStatus($ticket, 'in_progress')->assertOk();
        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_agent_cannot_move_closed_ticket_back_to_in_progress(): void
    {
        $ticket = $this->ticketAssignedToAgent('closed');
        Sanctum::actingAs($this->agent);

        $this->changeStatus($ticket, 'in_progress')->assertForbidden();
        $this->assertSame('closed', $ticket->fresh()->status);
    }

    public function test_agent_cannot_move_ticket_back_to_open(): void
    {
        $ticket = $this->ticketAssignedToAgent('in_progress');
        Sanctum::actingAs($this->agent);

        $this->changeStatus($ticket, 'open')->assertForbidden();
    }

    public function test_agent_cannot_change_status_of_unassigned_ticket(): void
    {
        $ticket = Ticket::factory()->create(['status' => 'open']);
        Sanctum::actingAs($this->agent);

        $this->changeStatus($ticket, 'in_progress')->assertForbidden();
    }

    public function test_admin_can_move_ticket_backward(): void
    {
        $ticket = $this->ticketAssignedToAgent('closed');
        Sanctum::actingAs(User::factory()->create()->assignRole('admin'));

        $this->changeStatus($ticket, 'in_progress')->assertOk();
    }
}
