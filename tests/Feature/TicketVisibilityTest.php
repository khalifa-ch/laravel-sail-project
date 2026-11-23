<?php

namespace Tests\Feature;

use App\Filament\Resources\Tickets\Pages\ListTickets;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class TicketVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private Ticket $assignedTicket;

    private Ticket $otherTicket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->agent = User::factory()->create()->assignRole('agent');

        $this->assignedTicket = Ticket::factory()->create(['assigned_agent_id' => $this->agent->id]);
        $this->otherTicket    = Ticket::factory()->create();
    }

    public function test_agent_only_sees_assigned_tickets_in_api_list(): void
    {
        Sanctum::actingAs($this->agent);

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->assignedTicket->id);
    }

    public function test_agent_only_sees_assigned_tickets_in_admin_panel(): void
    {
        $this->actingAs($this->agent);

        Livewire::test(ListTickets::class)
            ->assertCanSeeTableRecords([$this->assignedTicket])
            ->assertCanNotSeeTableRecords([$this->otherTicket]);
    }

    public function test_admin_sees_all_tickets_in_admin_panel(): void
    {
        $this->actingAs(User::factory()->create()->assignRole('admin'));

        Livewire::test(ListTickets::class)
            ->assertCanSeeTableRecords([$this->assignedTicket, $this->otherTicket]);
    }
}
