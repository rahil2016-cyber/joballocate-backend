<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\IndustryType;
use App\Models\JobPost;
use App\Models\JobSeekerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminIndustryTypeController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', Rule::in(['0', '1'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = IndustryType::query()->orderBy('sort_order')->orderBy('label');

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('key', 'like', $term)
                    ->orWhere('label', 'like', $term);
            });
        }
        if (($validated['is_active'] ?? null) === '0' || ($validated['is_active'] ?? null) === '1') {
            $q->where('is_active', $validated['is_active'] === '1');
        }

        $rows = $q->paginate((int) ($validated['per_page'] ?? 30));
        $data = collect($rows->items())->map(fn (IndustryType $r) => $this->toRow($r))->values()->all();

        return $this->ok(
            $data,
            'OK',
            [
                'current_page' => $rows->currentPage(),
                'last_page' => $rows->lastPage(),
                'per_page' => $rows->perPage(),
                'total' => $rows->total(),
            ]
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9_]+$/', 'unique:industry_types,key'],
            'label' => ['required', 'string', 'max:160'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $row = IndustryType::query()->create([
            'key' => $validated['key'],
            'label' => $validated['label'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? true),
        ]);

        return $this->ok($this->toRow($row), 'Industry type created.', null, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $row = IndustryType::query()->find($id);
        if (! $row) {
            return $this->fail('Industry type not found.', null, 404);
        }

        $validated = $request->validate([
            'label' => ['sometimes', 'required', 'string', 'max:160'],
            'sort_order' => ['sometimes', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        foreach (['label', 'sort_order', 'is_active'] as $f) {
            if (array_key_exists($f, $validated)) {
                $row->{$f} = $validated[$f];
            }
        }
        $row->save();

        return $this->ok($this->toRow($row->fresh()), 'Industry type updated.');
    }

    public function destroy(int $id): JsonResponse
    {
        $row = IndustryType::query()->find($id);
        if (! $row) {
            return $this->fail('Industry type not found.', null, 404);
        }

        $key = $row->key;
        $inUse = JobPost::query()->where('industry_type', $key)->exists()
            || Company::query()->where('industry_type', $key)->exists()
            || JobSeekerProfile::query()->where('industry_type', $key)->exists();

        if ($inUse) {
            return $this->fail(
                'This industry is in use. Deactivate it instead of deleting.',
                null,
                422
            );
        }

        $row->delete();

        return $this->ok(null, 'Industry type deleted.');
    }

    private function toRow(IndustryType $r): array
    {
        return [
            'id' => $r->id,
            'key' => $r->key,
            'label' => $r->label,
            'sort_order' => (int) $r->sort_order,
            'is_active' => (bool) $r->is_active,
            'created_at' => $r->created_at?->toIso8601String(),
            'updated_at' => $r->updated_at?->toIso8601String(),
        ];
    }
}
