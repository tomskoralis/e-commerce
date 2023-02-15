<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2023-1-1 00:00:00');
    }

    public function testRegister(): void
    {
        $response = $this->post('/api/v1/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'message' => 'Successfully registered.',
            'token' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => Hash::check('password', 'password'),
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'auth_token',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function testLogin(): void
    {
        $user = User::factory()->create();

        $response = $this->post('api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully logged in.',
            'token' => true,
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'auth_token',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function testUnauthenticatedCannotLogout(): void
    {
        $response = $this->post('api/v1/logout', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(401);

        $response->assertExactJson([
            'message' => 'Unauthenticated.',
        ]);
    }

    public function testLogout(): void
    {
        $response = $this->post('/api/v1/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
        ], [
            'Accept' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ]);

        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'auth_token',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $token = json_decode($response->getContent())->token;

        $response = $this->post('api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(200);

        $response->assertExactJson([
            'message' => 'Successfully logged out.',
        ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'name' => 'auth_token',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
