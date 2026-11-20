<?php

namespace Tests\Feature;

use App\Filament\Resources\Tickets\Pages\EditTicket;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTicketFormTest extends TestCase
{
    use RefreshDatabase;

    private Ticket $ticket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->ticket = Ticket::factory()->create();

        $this->actingAs(User::factory()->create()->assignRole('admin'));
    }

    public function test_admin_can_assign_an_agent_from_the_form(): void
    {
        $agent = User::factory()->create()->assignRole('agent');

        Livewire::test(EditTicket::class, ['record' => $this->ticket->getRouteKey()])
            ->fillForm(['assigned_agent_id' => $agent->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($agent->id, $this->ticket->fresh()->assigned_agent_id);
    }

    public function test_admin_cannot_assign_a_non_agent_from_the_form(): void
    {
        $client = User::factory()->create()->assignRole('client');

        Livewire::test(EditTicket::class, ['record' => $this->ticket->getRouteKey()])
            ->fillForm(['assigned_agent_id' => $client->id])
            ->call('save')
            ->assertHasFormErrors(['assigned_agent_id']);

        $this->assertNull($this->ticket->fresh()->assigned_agent_id);
    }
}
