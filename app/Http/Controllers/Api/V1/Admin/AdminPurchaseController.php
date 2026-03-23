<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\SeekerPackagePurchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminPurchaseController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kind' => ['nullable', Rule::in(['job_applications', 'resume', 'combo'])],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = SeekerPackagePurchase::query()
            ->with('user:id,name,email,phone,role')
            ->latest('activated_at')
            ->latest('id');

        if (! empty($validated['kind'] ?? null)) {
            $q->where('kind', $validated['kind']);
        }

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('title', 'like', $term)
                    ->orWhere('package_key', 'like', $term)
                    ->orWhereHas('user', function ($uQ) use ($term): void {
                        $uQ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }

        $rows = $q->paginate((int) ($validated['per_page'] ?? 25));

        return $this->ok(
            $rows->items(),
            'OK',
            [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ]
        );
    }
}

