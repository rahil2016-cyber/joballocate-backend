<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\CompanyCoupon;
use App\Models\CompanySubscriptionPackage;
use App\Models\CompanySubscriptionPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminCompanySubscriptionPackageController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $packages = CompanySubscriptionPackage::query()
            ->orderByDesc('sort_order')
            ->orderByDesc('id')
            ->get();

        $items = $packages->map(function (CompanySubscriptionPackage $p) {
            $couponCount = CompanyCoupon::query()
                ->where('company_subscription_package_id', $p->id)
                ->count();

            return [
                'id' => $p->id,
                'title' => $p->title,
                'monthly_price_inr' => (int) $p->monthly_price_inr,
                'is_active' => (bool) $p->is_active,
                'sort_order' => (int) $p->sort_order,
                'coupon_count' => (int) $couponCount,
            ];
        })->values()->all();

        return $this->ok(['items' => $items]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'monthly_price_inr' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $p = CompanySubscriptionPackage::create([
            'title' => trim($validated['title']),
            'monthly_price_inr' => (int) ($validated['monthly_price_inr'] ?? 499),
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return $this->ok(['package' => $p], 'Package created.');
    }

    public function update(Request $request, int $packageId): JsonResponse
    {
        $p = CompanySubscriptionPackage::find($packageId);
        if (! $p) {
            return $this->fail('Package not found.', null, 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:160'],
            'monthly_price_inr' => ['sometimes', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ]);

        $p->fill($validated);
        $p->save();

        return $this->ok(['package' => $p], 'Package updated.');
    }

    public function destroy(int $packageId): JsonResponse
    {
        $p = CompanySubscriptionPackage::find($packageId);
        if (! $p) {
            return $this->fail('Package not found.', null, 404);
        }

        $p->delete();

        return $this->ok(null, 'Package deleted.');
    }

    public function coupons(int $packageId, Request $request): JsonResponse
    {
        $coupons = CompanyCoupon::query()
            ->where('company_subscription_package_id', $packageId)
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
}

