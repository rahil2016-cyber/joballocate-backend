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

class CompanySubscriptionController extends Controller
{
    use ApiResponses;

    private function inferStateDistrictFromLocation(Company $company): array
    {
        [$state, $district] = $this->inferStateDistrictFromLocation($company);

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
        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $verified = $company->isVerified();

        $activePackage = CompanySubscriptionPackage::query()
            ->where('is_active', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        $packageId = $activePackage?->id;
        $monthlyPriceInr = (int) ($activePackage?->monthly_price_inr ?? 399);
        $packageTitle = (string) ($activePackage?->title ?? 'Company Subscription');

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
    }

    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $company = $request->user()->company;

        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        if (! $company->isVerified()) {
            return $this->fail('Company is not verified yet.', null, 403);
        }

        $activePackage = CompanySubscriptionPackage::query()
            ->where('is_active', true)
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->first();

        $packageId = $activePackage?->id;
        $packageTitle = (string) ($activePackage?->title ?? 'Company Subscription');
        $monthlyPriceInr = (int) ($activePackage?->monthly_price_inr ?? 399);

        $nextCycle = (int) (CompanySubscriptionPayment::query()
            ->when($packageId !== null, fn ($q) => $q
                ->where(function ($qq) use ($packageId): void {
                    $qq->where('company_subscription_package_id', $packageId)
                        ->orWhereNull('company_subscription_package_id');
                })
            )
            ->where('company_id', $company->id)
            ->max('cycle_number') ?? 0) + 1;

        $couponCode = $validated['coupon_code'] ?? null;
        $couponCodeUsed = $couponCode !== null && trim($couponCode) !== '' ? trim($couponCode) : null;

        $appliedCoupon = null;
        $isFree = false;
        $amount = $monthlyPriceInr;

        if ($nextCycle === 1) {
            // First month free is applied ONLY when admin-targeted coupon_code is provided.
            if ($couponCode) {
                $coupon = CompanyCoupon::query()
                    ->where('is_active', true)
                    ->when($packageId !== null, fn ($q) => $q
                        ->where(function ($qq) use ($packageId): void {
                            $qq->where('company_subscription_package_id', $packageId)
                                ->orWhereNull('company_subscription_package_id');
                        })
                    )
                    ->whereRaw('lower(code) = ?', [mb_strtolower(trim($couponCode))])
                    ->first();

                if ($coupon
                    && (bool) $coupon->free_first_month
                    && $this->matchCouponToCompany($coupon, $company)
                ) {
                    $appliedCoupon = $coupon;
                    $isFree = true;
                    $amount = 0;
                }
            }
        } else {
            // Renewal: coupon applies % discount on ₹399 if it matches state/district.
            if ($couponCode) {
                $coupon = CompanyCoupon::query()
                    ->where('is_active', true)
                    ->when($packageId !== null, fn ($q) => $q
                        ->where(function ($qq) use ($packageId): void {
                            $qq->where('company_subscription_package_id', $packageId)
                                ->orWhereNull('company_subscription_package_id');
                        })
                    )
                    ->whereRaw('lower(code) = ?', [mb_strtolower(trim($couponCode))])
                    ->first();

                if ($coupon
                    && $this->matchCouponToCompany($coupon, $company)
                    && (int) $coupon->discount_percent > 0
                ) {
                    $appliedCoupon = $coupon;
                    $discount = (int) $coupon->discount_percent;
                    $amount = (int) round($monthlyPriceInr * (1 - ($discount / 100)));
                    if ($amount < 0) $amount = 0;
                }
            }
        }

        // Persist payment record; month #1 = cycle_number 1
        $payment = CompanySubscriptionPayment::create([
            'company_id' => $company->id,
            'company_subscription_package_id' => $packageId,
            'cycle_number' => $nextCycle,
            'coupon_code_used' => $couponCodeUsed,
            'amount_inr' => $amount,
            'is_free' => $isFree,
            'purchased_at' => Carbon::now(),
        ]);

        return $this->ok([
            'package_title' => $packageTitle,
            'cycle_number' => $payment->cycle_number,
            'is_free' => $payment->is_free,
            'amount_inr' => (int) $payment->amount_inr,
            'applied_coupon_code' => $appliedCoupon?->code,
            'message' => $isFree ? '1st month activated for free.' : 'Subscription month purchased successfully.',
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $company = $request->user()->company;
        if (! $company) {
            return $this->fail('Company profile not found.', null, 404);
        }

        $rows = CompanySubscriptionPayment::query()
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
            ])
            ->values()
            ->all();

        return $this->ok(['items' => $rows]);
    }
}

