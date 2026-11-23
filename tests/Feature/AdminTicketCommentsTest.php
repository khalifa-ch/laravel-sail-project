<?php

namespace Tests\Feature;

use App\Filament\Resources\Tickets\Pages\ViewTicket;
use App\Filament\Resources\Tickets\RelationManagers\CommentsRelationManager;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTicketCommentsTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->agent  = User::factory()->create()->assignRole('agent');
        $this->client = User::factory()->create()->assignRole('client');

        $this->actingAs($this->agent);
    }

    private function ticket(string $status = 'in_progress'): Ticket
    {
        return Ticket::factory()->for($this->client, 'client')->create([
            'status'            => $status,
            'assigned_agent_id' => $this->agent->id,
        ]);
    }

    private function commentsOf(Ticket $ticket): Testable
    {
        return Livewire::test(CommentsRelationManager::class, [
            'ownerRecord' => $ticket,
            'pageClass'   => ViewTicket::class,
        ]);
    }

    public function test_agent_sees_comments_of_assigned_ticket(): void
    {
        $ticket  = $this->ticket();
        $comment = Comment::factory()->for($ticket)->for($this->client)->create();

        $this->commentsOf($ticket)
            ->assertCanSeeTableRecords([$comment])
            ->assertSee($comment->content);
    }

    public function test_agent_comment_is_authored_by_the_agent(): void
    {
        $ticket = $this->ticket();

        $this->commentsOf($ticket)
            ->callTableAction('create', data: ['content' => 'Je regarde ça.'])
            ->assertHasNoTableActionErrors();

        $comment = $ticket->comments()->sole();
        $this->assertSame($this->agent->id, $comment->user_id);
        $this->assertSame('Je regarde ça.', $comment->content);
    }

    public function test_agent_cannot_comment_on_closed_ticket(): void
    {
        $this->commentsOf($this->ticket('closed'))
            ->assertTableActionHidden('create');
    }

    public function test_agent_cannot_edit_or_delete_the_clients_comment(): void
    {
        $ticket  = $this->ticket();
        $comment = Comment::factory()->for($ticket)->for($this->client)->create();

        $this->commentsOf($ticket)
            ->assertTableActionHidden('edit', $comment)
            ->assertTableActionHidden('delete', $comment);
    }

    public function test_comments_cannot_be_moved_between_tickets(): void
    {
        $this->commentsOf($this->ticket())
            ->assertTableActionDoesNotExist('associate')
            ->assertTableActionDoesNotExist('dissociate');
    }
}
