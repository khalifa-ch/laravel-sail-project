<?php

namespace Tests\Feature;

use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTicketStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->agent = User::factory()->create()->assignRole('agent');
        $this->actingAs($this->agent);
    }

    private function ticketAssignedToAgent(string $status): Ticket
    {
        return Ticket::factory()->create([
            'status'            => $status,
            'assigned_agent_id' => $this->agent->id,
        ]);
    }

    public function test_agent_can_move_ticket_forward(): void
    {
        $ticket = $this->ticketAssignedToAgent('in_progress');

        Livewire::test(ListTickets::class)
            ->callTableAction('changeStatus', $ticket, data: ['status' => 'closed'])
            ->assertHasNoTableActionErrors();

        $this->assertSame('closed', $ticket->fresh()->status);
    }

    public function test_agent_cannot_move_ticket_backward(): void
    {
        $ticket = $this->ticketAssignedToAgent('in_progress');

        Livewire::test(ListTickets::class)
            ->callTableAction('changeStatus', $ticket, data: ['status' => 'open'])
            ->assertHasTableActionErrors(['status']);

        $this->assertSame('in_progress', $ticket->fresh()->status);
    }

    public function test_action_is_hidden_on_closed_ticket_for_agent(): void
    {
        $ticket = $this->ticketAssignedToAgent('closed');

        Livewire::test(ListTickets::class)
            ->assertTableActionHidden('changeStatus', $ticket);
    }

    public function test_admin_can_move_ticket_backward(): void
    {
        $ticket = $this->ticketAssignedToAgent('closed');
        $this->actingAs(User::factory()->create()->assignRole('admin'));

        Livewire::test(ListTickets::class)
            ->callTableAction('changeStatus', $ticket, data: ['status' => 'in_progress'])
            ->assertHasNoTableActionErrors();

        $this->assertSame('in_progress', $ticket->fresh()->status);
    }
}
