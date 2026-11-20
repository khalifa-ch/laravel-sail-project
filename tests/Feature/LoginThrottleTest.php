<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_is_blocked_after_too_many_attempts(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
                ->assertUnauthorized();
        }

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertTooManyRequests();
    }
}
