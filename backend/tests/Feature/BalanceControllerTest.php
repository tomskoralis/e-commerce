<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testUnauthenticatedCannotSeeBalance(): void
    {
        $response = $this->get('/api/v1/balance', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testShowBalance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/api/v1/balance', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            "balance" => "0.00"
        ]);
    }

    public function testUnauthenticatedCannotAddBalance(): void
    {
        $response = $this->put('/api/v1/balance', [
            'amount' => 12.34
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testAddBalance(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->put('/api/v1/balance', [
            'amount' => 12.34
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(200);
        $response->assertExactJson([
            'message' => 'Successfully updated the balance.',
            'balance' => '12.34'
        ]);
    }
}
