<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\UserRole;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['nullable', Rule::in([
                UserRole::JobSeeker->value,
                UserRole::Company->value,
                UserRole::SuperAdmin->value,
            ])],
            'is_active' => ['nullable', 'boolean'],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = User::query()->with(['company:id,user_id,name,verification_status', 'jobSeekerProfile:id,user_id,city,country']);

        if (array_key_exists('role', $validated) && ! empty($validated['role'])) {
            $q->where('role', $validated['role']);
        }
        if (array_key_exists('is_active', $validated)) {
            $q->where('is_active', (bool) $validated['is_active']);
        }
        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $rows = $q->latest('id')->paginate((int) ($validated['per_page'] ?? 20));

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

    public function show(int $userId): JsonResponse
    {
        $user = User::query()
            ->with([
                'company.jobPosts',
                'jobSeekerProfile',
                'applications.jobPost:id,title,company_id',
            ])
            ->find($userId);

        if (! $user) {
            return $this->fail('User not found.', null, 404);
        }

        return $this->ok($user);
    }

    public function updateStatus(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::query()->find($userId);
        if (! $user) {
            return $this->fail('User not found.', null, 404);
        }

        $user->is_active = (bool) $validated['is_active'];
        $user->save();

        return $this->ok($user->fresh(), 'User status updated.');
    }
}

