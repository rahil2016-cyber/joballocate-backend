<?php

namespace App\Http\Controllers\Api\V1\Company;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyCoupon;
use App\Models\CompanySubscriptionPayment;
use App\Models\CompanySubscriptionPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class CompanySubscriptionController extends Controller
{
    use ApiResponses;

    private function inferStateDistrictFromLocation(Company $company): array
    {
        $state = $company->state;
        $district = $company->district;

        if ((filled($state) && filled($district)) || ! filled($company->location)) {
            return [$state, $district];
        }

        $parts = array_values(array_filter(array_map(
            fn ($p) => trim((string) $p),
            explode(',', (string) $company->location)
        ), fn ($p) => $p !== ''));

        // Common legacy format: "city, district, state"
        if (! filled($state) && count($parts) >= 1) {
            $state = $parts[count($parts) - 1];
        }
        if (! filled($district) && count($parts) >= 2) {
            $district = $parts[count($parts) - 2];
        }

        return [$state, $district];
    }

    private function matchCouponToCompany(CompanyCoupon $coupon, Company $company): bool
    {
        $targetType = (string) $coupon->target_type;
        $needle = mb_strtolower(trim($coupon->target_value));

        if ($targetType === 'all') {
            return true;
        }

        if ($needle === '') {
            return false;
        }

        if ($targetType === 'state') {
            [$s] = $this->inferStateDistrictFromLocation($company);
            return $s !== null && mb_strtolower(trim($s)) === $needle;
        }

        if ($targetType === 'district') {
            [, $d] = $this->inferStateDistrictFromLocation($company);
            return $d !== null && mb_strtolower(trim($d)) === $needle;
        }

        return false;
    }

    public function offer(Request $request): JsonResponse
    {
        try {
            $company = $request->user()->company;

            if (! $company) {
                return $this->fail('Company profile not found.', null, 404);
            }

            $verified = $company->isVerified();

            $successfulPaymentsCount = $company->subscriptionPayments()
                ->where('payment_status', 'successful')
                ->count();

            $totalJobsCount = \App\Models\JobPost::query()
                ->where('company_id', $company->id)
                ->count();

            $hasActiveSubscription = $successfulPaymentsCount >= $totalJobsCount;

            $activePackage = CompanySubscriptionPackage::query()
                ->where('is_active', true)
                ->orderByDesc('sort_order')
                ->orderByDesc('id')
                ->first();

            $packageId = $activePackage?->id;
            $monthlyPriceInr = (int) ($activePackage?->monthly_price_inr ?? 499);
            $packageTitle = (string) ($activePackage?->title ?? 'Company Subscription');

            if ($packageId === null) {
                return $this->ok([
                    'verified' => $verified,
                    'has_active_subscription' => false,
                    'package_title' => $packageTitle,
                    'monthly_price_inr' => $monthlyPriceInr,
                    'first_month' => [
                        'already_purchased' => false,
                        'is_free_eligible' => false,
                        'eligible_coupon_codes' => [],
                        'suggested_coupon_code' => null,
                        'message' => 'No active subscription package configured by admin yet.',
                    ],
                ]);
            }

        $cycle1 = CompanySubscriptionPayment::query()
            ->when($packageId !== null, fn ($q) => $q
                ->where(function ($qq) use ($packageId): void {
                    $qq->where('company_subscription_package_id', $packageId)
                        ->orWhereNull('company_subscription_package_id');
                })
            )
            ->where('company_id', $company->id)
            ->where('cycle_number', 1)
            ->first();

        $alreadyPurchasedFirstMonth = $cycle1 !== null;

        [$state, $district] = $this->inferStateDistrictFromLocation($company);

        $eligibleCoupons = CompanyCoupon::query()
            ->where('is_active', true)
            ->when($packageId !== null, fn ($q) => $q
                ->where(function ($qq) use ($packageId): void {
                    $qq->where('company_subscription_package_id', $packageId)
                        ->orWhereNull('company_subscription_package_id');
                })
            )
            ->where('free_first_month', true)
            ->where(function ($q) use ($state, $district): void {
                // General "All India" coupons.
                $q->where('target_type', 'all');

                if ($state) {
                    $q->orWhere(function ($qq) use ($state): void {
                        $qq->where('target_type', 'state')
                            ->whereRaw('lower(target_value) = ?', [mb_strtolower(trim($state))]);
                    });
                }

                if ($district) {
                    $q->orWhere(function ($qq) use ($district): void {
                        $qq->where('target_type', 'district')
                            ->whereRaw('lower(target_value) = ?', [mb_strtolower(trim($district))]);
                    });
                }
            })
            ->orderByDesc('id')
            ->get();

        $eligibleFreeCoupons = $eligibleCoupons->values();

        if (! $verified) {
            return $this->ok([
                'verified' => false,
                'has_active_subscription' => $hasActiveSubscription,
                'package_title' => $packageTitle,
                'monthly_price_inr' => $monthlyPriceInr,
                'first_month' => [
                    'already_purchased' => $alreadyPurchasedFirstMonth,
                    'is_free_eligible' => false,
                    'eligible_coupon_codes' => [],
                    'suggested_coupon_code' => null,
                    'message' => 'Your company is not verified yet. Free month eligibility will appear after verification.',
                ],
                'renewal' => [
                    'message' => 'After your first purchase, coupon codes can give renewal discounts.',
                ],
            ]);
        }

        if ($alreadyPurchasedFirstMonth) {
            return $this->ok([
                'verified' => true,
                'has_active_subscription' => $hasActiveSubscription,
                'package_title' => $packageTitle,
                'monthly_price_inr' => $monthlyPriceInr,
                'first_month' => [
                    'already_purchased' => true,
                    'is_free_eligible' => false,
                    'eligible_coupon_codes' => [],
                    'suggested_coupon_code' => null,
                    'message' => 'You already used your 1st month slot.',
                ],
                'renewal' => [
                    'message' => 'Use an eligible coupon code for renewal discounts.',
                ],
            ]);
        }

        if ($eligibleFreeCoupons->isEmpty()) {
            return $this->ok([
                'verified' => true,
                'has_active_subscription' => $hasActiveSubscription,
                'package_title' => $packageTitle,
                'monthly_price_inr' => $monthlyPriceInr,
                'first_month' => [
                    'already_purchased' => false,
                    'is_free_eligible' => false,
                    'eligible_coupon_codes' => [],
                    'suggested_coupon_code' => null,
                    'message' => 'Your state/district is not eligible for 1st month free. First month price is ₹'.$monthlyPriceInr.'.',
                ],
                'renewal' => [
                    'message' => 'For next months, eligible coupons can provide % renewal discounts.',
                ],
            ]);
        }

        $suggested = $eligibleFreeCoupons->first();

            return $this->ok([
            'verified' => true,
            'has_active_subscription' => $hasActiveSubscription,
            'package_title' => $packageTitle,
            'monthly_price_inr' => $monthlyPriceInr,
            'first_month' => [
                'already_purchased' => false,
                'is_free_eligible' => true,
                'eligible_coupon_codes' => $eligibleFreeCoupons->map(fn (CompanyCoupon $c) => $c->code)->values()->all(),
                'suggested_coupon_code' => $suggested->code,
                'message' => 'You are eligible for 1st month free. Use coupon code to activate.',
            ],
            'renewal' => [
                'message' => 'For next months, eligible coupons can provide % renewal discounts.',
            ],
            ]);
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage() ?: 'Failed to load subscription offer.', null, 500);
        }
    }

    public function purchase(Request $request): JsonResponse
    {
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $activePackage = CompanySubscriptionPackage::query()
            ->where('is_active', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        $packageId = $activePackage?->id;
        $packageTitle = (string) ($activePackage?->title ?? 'Company Subscription');
        $monthlyPriceInr = (int) ($activePackage?->monthly_price_inr ?? 499);

        $nextCycle = (int) (CompanySubscriptionPayment::query()
            ->when($packageId !== null, fn ($q) => $q
                ->where(function ($qq) use ($packageId): void {
                    $qq->where('company_subscription_package_id', $packageId)
                        ->orWhereNull('company_subscription_package_id');
                })
            )
            ->where('company_id', $company->id)
            ->max('cycle_number') ?? 0) + 1;

        try {
            $api = app(Api::class);
            
            // Monthly Price + 18% GST (1 INR = 100 paise)
            $amountInPaise = (int) round($monthlyPriceInr * 1.18 * 100);

            // Create order in Razorpay
            $razorpayOrder = $api->order->create([
                'receipt' => 'sub_' . $company->id . '_' . time(),
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'notes' => [
                    'company_id' => $company->id,
                    'package_id' => $packageId,
                    'package_title' => $packageTitle,
                ]
            ]);

            $orderId = $razorpayOrder['id'];

            // Log the purchase as pending
            $payment = CompanySubscriptionPayment::create([
                'company_id' => $company->id,
                'company_subscription_package_id' => $packageId,
                'cycle_number' => $nextCycle,
                'coupon_code_used' => null,
                'amount_inr' => (int) round($monthlyPriceInr * 1.18),
                'is_free' => false,
                'payment_status' => 'pending',
                'razorpay_order_id' => $orderId,
                'purchased_at' => null,
            ]);

            return $this->ok([
                'order_id' => $orderId,
                'amount' => $amountInPaise,
                'currency' => 'INR',
                'package_title' => $packageTitle,
                'key_id' => config('services.razorpay.key_id'),
            ], 'Razorpay order created successfully.');

        } catch (\Exception $e) {
            return $this->fail('Razorpay order creation failed: ' . $e->getMessage(), null, 500);
        }
    }

    public function verifySignature(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $company = $request->user()->company;
        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $payment = CompanySubscriptionPayment::query()
            ->where('razorpay_order_id', $validated['razorpay_order_id'])
            ->where('company_id', $company->id)
            ->first();

        if (!$payment) {
            return $this->fail('Order not found.', null, 404);
        }

        if ($payment->payment_status === 'successful') {
            return $this->ok(null, 'Payment already verified.');
        }

        try {
            $api = app(Api::class);

            // Verify signature
            $attributes = [
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature']
            ];

            $api->utility->verifyPaymentSignature($attributes);

            $payment->update([
                'payment_status' => 'successful',
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'purchased_at' => now(),
            ]);

            try {
                $user = $request->user();
                if ($user->email && !\App\Support\Identifier::isSyntheticEmail($user->email)) {
                    \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\CompanySubscriptionSuccessMail($payment));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('[CompanySubscriptionController] Failed to send email: ' . $e->getMessage());
            }

            return $this->ok(null, 'Payment verified successfully.');

        } catch (SignatureVerificationError $e) {
            $payment->update([
                'payment_status' => 'failed',
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ]);

            return $this->fail('Signature verification failed.', null, 400);
        } catch (\Exception $e) {
            return $this->fail('Verification error: ' . $e->getMessage(), null, 500);
        }
    }

    public function history(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $rows = CompanySubscriptionPayment::query()
            ->with('package:id,title')
            ->where('company_id', $company->id)
            ->orderByDesc('id')
            ->limit(60)
            ->get()
            ->map(fn (CompanySubscriptionPayment $p) => [
                'id' => $p->id,
                'cycle_number' => (int) $p->cycle_number,
                'amount_inr' => (int) $p->amount_inr,
                'is_free' => (bool) $p->is_free,
                'coupon_code_used' => $p->coupon_code_used,
                'purchased_at' => $p->purchased_at?->toISOString(),
                'package_id' => $p->company_subscription_package_id,
                'package_title' => $p->package?->title,
            ])
            ->values()
            ->all();

        return $this->ok(['items' => $rows]);
    }
}

