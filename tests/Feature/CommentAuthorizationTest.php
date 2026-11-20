<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CommentAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_client_cannot_list_comments_of_another_clients_ticket(): void
    {
        $owner    = User::factory()->create()->assignRole('client');
        $stranger = User::factory()->create()->assignRole('client');

        $ticket = Ticket::factory()->for($owner, 'client')->create();
        Comment::factory()->for($ticket)->for($owner)->create();

        Sanctum::actingAs($stranger);

        $this->getJson("/api/tickets/{$ticket->id}/comments")->assertForbidden();
    }

    public function test_client_can_list_comments_of_own_ticket(): void
    {
        $owner = User::factory()->create()->assignRole('client');

        $ticket = Ticket::factory()->for($owner, 'client')->create();
        Comment::factory()->for($ticket)->for($owner)->create();

        Sanctum::actingAs($owner);

        $this->getJson("/api/tickets/{$ticket->id}/comments")->assertOk();
    }
}
