<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    private User $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->client = User::factory()->create()->assignRole('client');
        Sanctum::actingAs($this->client);
    }

    public function test_ticket_list_includes_pagination_metadata(): void
    {
        Ticket::factory()->count(20)->for($this->client, 'client')->create();

        $this->getJson('/api/tickets')
            ->assertOk()
            ->assertJsonCount(15, 'data')
            ->assertJsonPath('meta.total', 20)
            ->assertJsonPath('meta.last_page', 2);
    }

    public function test_comment_list_includes_pagination_metadata(): void
    {
        $ticket = Ticket::factory()->for($this->client, 'client')->create();
        Comment::factory()->count(25)->for($ticket)->for($this->client)->create();

        $this->getJson("/api/tickets/{$ticket->id}/comments")
            ->assertOk()
            ->assertJsonCount(20, 'data')
            ->assertJsonPath('meta.total', 25);
    }
}
