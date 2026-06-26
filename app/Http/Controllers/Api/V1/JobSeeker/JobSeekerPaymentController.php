<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Models\SeekerPackage;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class JobSeekerPaymentController extends Controller
{
    use ApiResponses;

    /**
     * Get Razorpay API Instance
     */
    protected function getRazorpayApi(): Api
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (!$keyId || !$keySecret) {
            throw new \Exception('Razorpay API credentials are not configured.');
        }

        return app(Api::class);
    }

    /**
     * Create Razorpay Order
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_key' => ['required', 'string', 'max:64'],
        ]);

        $user = $request->user();
        $pkg = SeekerPackage::query()
            ->where('key', $validated['package_key'])
            ->where('is_active', true)
            ->first();

        if (!$pkg) {
            return $this->fail('Package not found or inactive.', null, 404);
        }

        if ($pkg->kind !== 'resume') {
            return $this->fail('Only resume plans are supported.', null, 422);
        }

        try {
            $api = $this->getRazorpayApi();
            
            // Amount in paise (1 INR = 100 paise)
            $amountInPaise = (int) ($pkg->price_inr * 100);

            // Create order in Razorpay
            $razorpayOrder = $api->order->create([
                'receipt' => 'pkg_' . $user->id . '_' . time(),
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'notes' => [
                    'user_id' => $user->id,
                    'package_key' => $pkg->key,
                    'package_title' => $pkg->title,
                ]
            ]);

            $orderId = $razorpayOrder['id'];

            // Log the purchase as pending
            SeekerPackagePurchase::query()->create([
                'user_id' => $user->id,
                'seeker_package_id' => $pkg->id,
                'package_key' => $pkg->key,
                'title' => $pkg->title,
                'kind' => $pkg->kind,
                'price_inr' => $pkg->price_inr,
                'duration_days' => $pkg->duration_days,
                'applications_granted' => (int) $pkg->applications_included,
                'resume_builds_granted' => (int) $pkg->resume_builds_included,
                'payment_status' => 'pending',
                'razorpay_order_id' => $orderId,
                'activated_at' => null,
                'expires_at' => null,
            ]);

            return $this->ok([
                'order_id' => $orderId,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'package_key' => $pkg->key,
                'package_title' => $pkg->title,
                'key_id' => config('services.razorpay.key_id'),
            ], 'Razorpay order created successfully.');

        } catch (\Exception $e) {
            return $this->fail('Razorpay order creation failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Verify payment signature and activate package
     */
    public function verifySignature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $purchase = SeekerPackagePurchase::query()
            ->where('razorpay_order_id', $validated['razorpay_order_id'])
            ->first();

        if (!$purchase) {
            return $this->fail('Order not found.', null, 404);
        }

        if ($purchase->payment_status === 'successful') {
            return $this->ok(null, 'Payment already verified and package active.');
        }

        try {
            $api = $this->getRazorpayApi();

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature']
            ];

            $api->utility->verifyPaymentSignature($attributes);

            $purchase->activate($validated['razorpay_payment_id'], $validated['razorpay_signature']);

            $profile = $purchase->user->jobSeekerProfile;

            return $this->ok($profile ? $profile->fresh() : null, 'Payment verified and package activated successfully.');

        } catch (SignatureVerificationError $e) {
            $purchase->update([
                'payment_status' => 'failed',
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ]);

            return $this->fail('Signature verification failed. Payment was not authenticated.', null, 400);
        } catch (\Exception $e) {
            return $this->fail('Verification error: ' . $e->getMessage(), null, 500);
        }
    }
}
