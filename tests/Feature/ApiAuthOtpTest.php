<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_send_otp_returns_mock_code_when_debug_is_true(): void
    {
        config(['app.debug' => true]);

        $response = $this->postJson('/api/v1/auth/send-otp', [
            'identifier' => 'seeker@example.com',
            'intent' => 'register',
            'role' => 'job_seeker',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['mock_otp', 'expires_in_seconds']]);

        $this->assertNotEmpty($response->json('data.mock_otp'));
        $this->assertSame(6, strlen($response->json('data.mock_otp')));
    }

    public function test_register_and_verify_with_otp_creates_user_and_returns_token(): void
    {
        config(['app.debug' => true]);

        $send = $this->postJson('/api/v1/auth/send-otp', [
            'identifier' => 'newuser@example.com',
            'intent' => 'register',
            'role' => 'job_seeker',
        ]);

        $send->assertOk();
        $code = $send->json('data.mock_otp');

        $verify = $this->postJson('/api/v1/auth/verify-otp', [
            'identifier' => 'newuser@example.com',
            'code' => $code,
            'intent' => 'register',
            'role' => 'job_seeker',
            'name' => 'Test User',
            'state' => 'Karnataka',
            'district' => 'Davanagere',
        ]);

        $verify->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'role' => 'job_seeker',
        ]);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_me_returns_authenticated_user(): void
    {
        $user = User::factory()->create([
            'role' => 'job_seeker',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/me');

        $response->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }
}
