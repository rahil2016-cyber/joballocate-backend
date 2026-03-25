<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\CompanyCoupon;
use App\Models\CompanySubscriptionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCompanyCouponController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $packageId = $request->query('package_id');

        $q = CompanyCoupon::query();
        if ($packageId !== null && $packageId !== '') {
            $q->where('company_subscription_package_id', (int) $packageId);
        }

        $coupons = $q
            ->orderByDesc('id')
            ->get();

        $rows = $coupons->map(function (CompanyCoupon $c) {
            $purchasesCount = CompanySubscriptionPayment::query()
                ->where('coupon_code_used', $c->code)
                ->count();

            $totalIncome = CompanySubscriptionPayment::query()
                ->where('coupon_code_used', $c->code)
                ->sum('amount_inr');

            return [
                'id' => $c->id,
                'code' => $c->code,
                'company_subscription_package_id' => $c->company_subscription_package_id,
                'target_type' => $c->target_type,
                'target_value' => $c->target_value,
                'discount_percent' => (int) $c->discount_percent,
                'free_first_month' => (bool) $c->free_first_month,
                'is_active' => (bool) $c->is_active,
                'purchases_count' => (int) $purchasesCount,
                'total_income_inr' => (int) $totalIncome,
                'created_at' => $c->created_at?->toISOString(),
            ];
        })->values()->all();

        return $this->ok(['items' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_id' => ['nullable', 'integer', 'exists:company_subscription_packages,id'],
            'code' => ['required', 'string', 'max:64', 'unique:company_coupons,code'],
            'target_type' => ['required', Rule::in(['all', 'state', 'district'])],
            'target_value' => ['nullable', 'string', 'max:120'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'free_first_month' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $targetType = $validated['target_type'];
        $targetValue = $validated['target_value'] ?? '';
        if ($targetType === 'all') {
            $targetValue = 'ALL';
        } else {
            // For state/district, require a target_value.
            if (trim((string) $targetValue) === '') {
                return $this->fail('target_value is required for state/district coupons.', null, 422);
            }
            $targetValue = trim((string) $targetValue);
        }

        $coupon = CompanyCoupon::create([
            'code' => trim($validated['code']),
            'company_subscription_package_id' => $validated['package_id'] ?? null,
            'target_type' => $targetType,
            'target_value' => $targetValue,
            'discount_percent' => (int) ($validated['discount_percent'] ?? 0),
            'free_first_month' => (bool) ($validated['free_first_month'] ?? true),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return $this->ok(['coupon' => $coupon], 'Coupon created.');
    }

    public function destroy(Request $request, int $couponId): JsonResponse
    {
        $coupon = CompanyCoupon::find($couponId);
        if (! $coupon) {
            return $this->fail('Coupon not found.', null, 404);
        }

        $coupon->delete();

        return $this->ok(null, 'Coupon deleted.');
    }
}

