<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title'             => fake()->sentence(4),
            'description'       => fake()->paragraph(),
            'status'            => 'open',
            'priority'          => fake()->randomElement(['low', 'medium', 'high']),
            'client_id'         => User::factory(),
            'assigned_agent_id' => null,
        ];
    }
}
