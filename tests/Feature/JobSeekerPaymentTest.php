<?php

namespace Tests\Feature;

use App\Models\SeekerPackage;
use App\Models\SeekerPackagePurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Tests\TestCase;

class JobSeekerPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.razorpay.key_id' => 'rzp_test_123',
            'services.razorpay.key_secret' => 'secret_123',
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_order_requires_authentication(): void
    {
        $response = $this->postJson('/api/v1/job-seeker/payments/create-order', [
            'package_key' => 'basic_resume',
        ]);

        $response->assertStatus(401);
    }

    public function test_create_order_fails_with_invalid_package(): void
    {
        $user = User::factory()->create(['role' => 'job_seeker']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/job-seeker/payments/create-order', [
            'package_key' => 'invalid_package_key',
        ]);

        $response->assertStatus(404);
    }

    public function test_create_order_succeeds_and_creates_pending_purchase(): void
    {
        $user = User::factory()->create(['role' => 'job_seeker']);
        Sanctum::actingAs($user);

        // Create a test package
        $pkg = SeekerPackage::create([
            'key' => 'basic_resume',
            'title' => 'Basic Resume',
            'description' => 'Test',
            'kind' => 'resume',
            'price_inr' => 99,
            'duration_days' => 30,
            'applications_included' => 0,
            'resume_builds_included' => 5,
            'is_active' => true,
        ]);

        // Mock Razorpay Api
        $mockApi = Mockery::mock(Api::class);
        $mockOrders = Mockery::mock();
        $mockOrders->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function ($args) use ($user, $pkg) {
                return $args['amount'] === 9900 
                    && $args['currency'] === 'INR'
                    && $args['notes']['package_key'] === $pkg->key;
            }))
            ->andReturn([
                'id' => 'order_test_123',
            ]);
        $mockApi->order = $mockOrders;

        $this->app->instance(Api::class, $mockApi);

        $response = $this->postJson('/api/v1/job-seeker/payments/create-order', [
            'package_key' => 'basic_resume',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_id', 'order_test_123')
            ->assertJsonPath('data.amount', 9900);

        $this->assertDatabaseHas('seeker_package_purchases', [
            'user_id' => $user->id,
            'package_key' => 'basic_resume',
            'payment_status' => 'pending',
            'razorpay_order_id' => 'order_test_123',
            'activated_at' => null,
            'expires_at' => null,
        ]);
    }

    public function test_verify_signature_succeeds_and_activates_credits(): void
    {
        $user = User::factory()->create(['role' => 'job_seeker']);
        Sanctum::actingAs($user);

        // Create a test package
        $pkg = SeekerPackage::create([
            'key' => 'basic_resume',
            'title' => 'Basic Resume',
            'description' => 'Test',
            'kind' => 'resume',
            'price_inr' => 99,
            'duration_days' => 30,
            'applications_included' => 0,
            'resume_builds_included' => 5,
            'is_active' => true,
        ]);

        // Create pending purchase
        $purchase = SeekerPackagePurchase::create([
            'user_id' => $user->id,
            'seeker_package_id' => $pkg->id,
            'package_key' => $pkg->key,
            'title' => $pkg->title,
            'kind' => $pkg->kind,
            'price_inr' => $pkg->price_inr,
            'duration_days' => $pkg->duration_days,
            'applications_granted' => 0,
            'resume_builds_granted' => 5,
            'payment_status' => 'pending',
            'razorpay_order_id' => 'order_test_123',
            'activated_at' => null,
            'expires_at' => null,
        ]);

        // Mock Razorpay Api signature verification
        $mockApi = Mockery::mock(Api::class);
        $mockUtility = Mockery::mock();
        $mockUtility->shouldReceive('verifyPaymentSignature')
            ->once()
            ->with([
                'razorpay_order_id' => 'order_test_123',
                'razorpay_payment_id' => 'pay_test_999',
                'razorpay_signature' => 'sig_test_111',
            ])
            ->andReturnNull();
        $mockApi->utility = $mockUtility;

        $this->app->instance(Api::class, $mockApi);

        $response = $this->postJson('/api/v1/job-seeker/payments/verify-signature', [
            'razorpay_order_id' => 'order_test_123',
            'razorpay_payment_id' => 'pay_test_999',
            'razorpay_signature' => 'sig_test_111',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Assert purchase status is successful
        $this->assertDatabaseHas('seeker_package_purchases', [
            'id' => $purchase->id,
            'payment_status' => 'successful',
            'razorpay_payment_id' => 'pay_test_999',
            'razorpay_signature' => 'sig_test_111',
        ]);

        // Assert profile has updated credits
        $this->assertDatabaseHas('job_seeker_profiles', [
            'user_id' => $user->id,
            'resume_builds_remaining' => 5,
            'resume_package_key' => 'basic_resume',
        ]);
    }

    public function test_verify_signature_fails_updates_payment_status_to_failed(): void
    {
        $user = User::factory()->create(['role' => 'job_seeker']);
        Sanctum::actingAs($user);

        // Create a test package
        $pkg = SeekerPackage::create([
            'key' => 'basic_resume',
            'title' => 'Basic Resume',
            'description' => 'Test',
            'kind' => 'resume',
            'price_inr' => 99,
            'duration_days' => 30,
            'applications_included' => 0,
            'resume_builds_included' => 5,
            'is_active' => true,
        ]);

        // Create pending purchase
        $purchase = SeekerPackagePurchase::create([
            'user_id' => $user->id,
            'seeker_package_id' => $pkg->id,
            'package_key' => $pkg->key,
            'title' => $pkg->title,
            'kind' => $pkg->kind,
            'price_inr' => $pkg->price_inr,
            'duration_days' => $pkg->duration_days,
            'applications_granted' => 0,
            'resume_builds_granted' => 5,
            'payment_status' => 'pending',
            'razorpay_order_id' => 'order_test_123',
            'activated_at' => null,
            'expires_at' => null,
        ]);

        // Mock Razorpay Api signature verification throwing exception
        $mockApi = Mockery::mock(Api::class);
        $mockUtility = Mockery::mock();
        $mockUtility->shouldReceive('verifyPaymentSignature')
            ->once()
            ->andThrow(new SignatureVerificationError('Invalid signature'));
        $mockApi->utility = $mockUtility;

        $this->app->instance(Api::class, $mockApi);

        $response = $this->postJson('/api/v1/job-seeker/payments/verify-signature', [
            'razorpay_order_id' => 'order_test_123',
            'razorpay_payment_id' => 'pay_test_999',
            'razorpay_signature' => 'sig_invalid',
        ]);

        $response->assertStatus(400);

        // Assert purchase status is failed
        $this->assertDatabaseHas('seeker_package_purchases', [
            'id' => $purchase->id,
            'payment_status' => 'failed',
        ]);
    }

    public function test_webhook_fails_without_signature(): void
    {
        config(['services.razorpay.webhook_secret' => 'webhook_secret_123']);

        $response = $this->postJson('/api/v1/payments/webhook', [
            'event' => 'order.paid',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_fails_with_invalid_signature(): void
    {
        config(['services.razorpay.webhook_secret' => 'webhook_secret_123']);

        $mockApi = Mockery::mock(Api::class);
        $mockUtility = Mockery::mock();
        $mockUtility->shouldReceive('verifyWebhookSignature')
            ->once()
            ->andThrow(new SignatureVerificationError('Invalid webhook signature'));
        $mockApi->utility = $mockUtility;

        $this->app->instance(Api::class, $mockApi);

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'invalid_signature_here',
        ])->postJson('/api/v1/payments/webhook', [
            'event' => 'order.paid',
        ]);

        $response->assertStatus(400);
    }

    public function test_webhook_processes_order_paid_event_and_activates_package(): void
    {
        config(['services.razorpay.webhook_secret' => 'webhook_secret_123']);

        $user = User::factory()->create(['role' => 'job_seeker']);

        // Create a test package
        $pkg = SeekerPackage::create([
            'key' => 'basic_resume',
            'title' => 'Basic Resume',
            'description' => 'Test',
            'kind' => 'resume',
            'price_inr' => 99,
            'duration_days' => 30,
            'applications_included' => 0,
            'resume_builds_included' => 5,
            'is_active' => true,
        ]);

        // Create pending purchase
        $purchase = SeekerPackagePurchase::create([
            'user_id' => $user->id,
            'seeker_package_id' => $pkg->id,
            'package_key' => $pkg->key,
            'title' => $pkg->title,
            'kind' => $pkg->kind,
            'price_inr' => $pkg->price_inr,
            'duration_days' => $pkg->duration_days,
            'applications_granted' => 0,
            'resume_builds_granted' => 5,
            'payment_status' => 'pending',
            'razorpay_order_id' => 'order_webhook_123',
            'activated_at' => null,
            'expires_at' => null,
        ]);

        // Mock Razorpay webhook signature verification passing
        $mockApi = Mockery::mock(Api::class);
        $mockUtility = Mockery::mock();
        $mockUtility->shouldReceive('verifyWebhookSignature')
            ->once()
            ->andReturnNull();
        $mockApi->utility = $mockUtility;

        $this->app->instance(Api::class, $mockApi);

        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'valid_webhook_signature',
        ])->postJson('/api/v1/payments/webhook', [
            'event' => 'order.paid',
            'payload' => [
                'order' => [
                    'entity' => [
                        'id' => 'order_webhook_123',
                    ],
                ],
                'payment' => [
                    'entity' => [
                        'id' => 'pay_webhook_999',
                    ],
                ],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        // Assert purchase status is successful
        $this->assertDatabaseHas('seeker_package_purchases', [
            'id' => $purchase->id,
            'payment_status' => 'successful',
            'razorpay_payment_id' => 'pay_webhook_999',
        ]);

        // Assert profile has updated credits
        $this->assertDatabaseHas('job_seeker_profiles', [
            'user_id' => $user->id,
            'resume_builds_remaining' => 5,
            'resume_package_key' => 'basic_resume',
        ]);
    }
}
