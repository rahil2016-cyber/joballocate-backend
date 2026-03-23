<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\SeekerPackage;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminSeekerPackageController extends Controller
{
    use ApiResponses;

    public function index(): JsonResponse
    {
        $packages = SeekerPackage::query()->ordered()->get();

        $stats = SeekerPackagePurchase::query()
            ->selectRaw('package_key, COUNT(*) as purchase_count, COALESCE(SUM(price_inr), 0) as total_revenue_inr')
            ->groupBy('package_key')
            ->get()
            ->keyBy('package_key');

        $data = $packages->map(function (SeekerPackage $p) use ($stats) {
            $s = $stats->get($p->key);

            return [
                'id' => $p->id,
                'key' => $p->key,
                'title' => $p->title,
                'description' => $p->description,
                'kind' => $p->kind,
                'price_inr' => (int) $p->price_inr,
                'list_price_inr' => $p->list_price_inr !== null ? (int) $p->list_price_inr : null,
                'duration_days' => (int) $p->duration_days,
                'applications_included' => (int) $p->applications_included,
                'resume_builds_included' => (int) $p->resume_builds_included,
                'is_active' => (bool) $p->is_active,
                'sort_order' => (int) $p->sort_order,
                'created_at' => $p->created_at?->toIso8601String(),
                'updated_at' => $p->updated_at?->toIso8601String(),
                'purchase_count' => (int) ($s->purchase_count ?? 0),
                'total_revenue_inr' => (int) ($s->total_revenue_inr ?? 0),
            ];
        })->values()->all();

        return $this->ok($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/', 'unique:seeker_packages,key'],
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'kind' => ['required', Rule::in(['job_applications', 'resume', 'combo'])],
            'price_inr' => ['required', 'integer', 'min:0', 'max:10000000'],
            'list_price_inr' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:3650'],
            'applications_included' => ['required', 'integer', 'min:0', 'max:10000'],
            'resume_builds_included' => ['required', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ]);

        if (! empty($validated['list_price_inr']) && $validated['list_price_inr'] < $validated['price_inr']) {
            // allow list > price for offers; if list < price, swap semantics confusion — still allow
        }

        $row = SeekerPackage::query()->create([
            'key' => $validated['key'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'kind' => $validated['kind'],
            'price_inr' => $validated['price_inr'],
            'list_price_inr' => $validated['list_price_inr'] ?? null,
            'duration_days' => $validated['duration_days'],
            'applications_included' => $validated['applications_included'],
            'resume_builds_included' => $validated['resume_builds_included'],
            'is_active' => (bool) ($validated['is_active'] ?? true),
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ]);

        return $this->ok($row->fresh(), 'Package created.', null, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = SeekerPackage::query()->find($id);
        if (! $row) {
            return $this->fail('Package not found.', null, 404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'kind' => ['sometimes', Rule::in(['job_applications', 'resume', 'combo'])],
            'price_inr' => ['sometimes', 'integer', 'min:0', 'max:10000000'],
            'list_price_inr' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:10000000'],
            'duration_days' => ['sometimes', 'integer', 'min:1', 'max:3650'],
            'applications_included' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'resume_builds_included' => ['sometimes', 'integer', 'min:0', 'max:10000'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
        ]);

        $row->fill($validated);
        $row->save();

        return $this->ok($row->fresh(), 'Package updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $row = SeekerPackage::query()->find($id);
        if (! $row) {
            return $this->fail('Package not found.', null, 404);
        }

        DB::transaction(function () use ($row): void {
            SeekerPackagePurchase::query()
                ->where('seeker_package_id', $row->id)
                ->update(['seeker_package_id' => null]);
            $row->delete();
        });

        return $this->ok(null, 'Package deleted.');
    }
}
