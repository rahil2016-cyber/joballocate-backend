<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAuthPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_verify_set_password_and_login_with_password(): void
    {
        config(['app.debug' => true]);

        // 1. Send OTP
        $send = $this->postJson('/api/v1/auth/send-otp', [
            'identifier' => 'newuser@example.com',
            'intent' => 'register',
            'role' => 'job_seeker',
        ]);
        $send->assertOk();
        $code = $send->json('data.mock_otp');

        // 2. Verify OTP
        $verify = $this->postJson('/api/v1/auth/verify-otp', [
            'identifier' => 'newuser@example.com',
            'code' => $code,
            'intent' => 'register',
            'role' => 'job_seeker',
            'name' => 'Test User',
            'state' => 'Karnataka',
            'district' => 'Bengaluru',
        ]);
        $verify->assertOk();
        $token = $verify->json('data.token');

        // 3. Set password
        $set = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/v1/auth/set-password', [
            'password' => 'Password123!',
        ]);
        
        $set->assertOk();

        // 4. Login with password
        $login = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'newuser@example.com',
            'password' => 'Password123!',
            'role' => 'job_seeker',
        ]);

        $login->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }
}
