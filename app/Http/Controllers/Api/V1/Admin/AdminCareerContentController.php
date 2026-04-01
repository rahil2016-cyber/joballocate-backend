<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\CareerContent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCareerContentController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', Rule::in([
                CareerContent::TYPE_CAREER_GUIDANCE,
                CareerContent::TYPE_INTERVIEW_EXPERIENCE,
                CareerContent::TYPE_INTERVIEW_QA,
            ])],
            'search' => ['nullable', 'string', 'max:120'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = CareerContent::query()->orderBy('sort_order')->orderByDesc('id');

        if (! empty($validated['type'] ?? null)) {
            $q->where('content_type', $validated['type']);
        }
        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('title', 'like', $term)
                    ->orWhere('subtitle', 'like', $term)
                    ->orWhere('body', 'like', $term)
                    ->orWhere('question', 'like', $term)
                    ->orWhere('answer', 'like', $term)
                    ->orWhere('category', 'like', $term);
            });
        }

        $rows = $q->paginate((int) ($validated['per_page'] ?? 25));
        $data = collect($rows->items())->map(fn (CareerContent $c) => $this->toAdminPayload($c))->values()->all();

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
        $row = CareerContent::query()->create($this->validatePayload($request));

        return $this->ok($this->toAdminPayload($row), 'Created', null, 201);
    }

    public function update(Request $request, CareerContent $careerContent): JsonResponse
    {
        $careerContent->update($this->validatePayload($request));

        return $this->ok($this->toAdminPayload($careerContent->fresh()), 'Updated');
    }

    public function destroy(CareerContent $careerContent): JsonResponse
    {
        $careerContent->delete();

        return $this->ok(null, 'Deleted');
    }

    /** @return array<string, mixed> */
    private function validatePayload(Request $request): array
    {
        $base = $request->validate([
            'content_type' => ['required', 'string', Rule::in([
                CareerContent::TYPE_CAREER_GUIDANCE,
                CareerContent::TYPE_INTERVIEW_EXPERIENCE,
                CareerContent::TYPE_INTERVIEW_QA,
            ])],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_published' => ['nullable', 'boolean'],
            'published_at' => ['nullable', 'date'],
        ]);

        $type = $base['content_type'];

        if ($type === CareerContent::TYPE_INTERVIEW_QA) {
            $extra = $request->validate([
                'category' => ['required', 'string', 'max:120'],
                'question' => ['required', 'string', 'max:500'],
                'answer' => ['required', 'string', 'max:65000'],
            ]);

            return [
                'content_type' => $type,
                'category' => $extra['category'],
                'title' => null,
                'subtitle' => null,
                'body' => null,
                'question' => $extra['question'],
                'answer' => $extra['answer'],
                'rating_hint' => null,
                'sort_order' => (int) ($base['sort_order'] ?? 0),
                'is_published' => (bool) ($base['is_published'] ?? true),
                'published_at' => $base['published_at'] ?? null,
            ];
        }

        $extra = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65000'],
            'rating_hint' => ['nullable', 'numeric', 'min:0', 'max:5'],
        ]);

        return [
            'content_type' => $type,
            'category' => null,
            'title' => $extra['title'],
            'subtitle' => $extra['subtitle'] ?? null,
            'body' => $extra['body'],
            'question' => null,
            'answer' => null,
            'rating_hint' => isset($extra['rating_hint']) && $extra['rating_hint'] !== null
                ? round((float) $extra['rating_hint'], 1)
                : null,
            'sort_order' => (int) ($base['sort_order'] ?? 0),
            'is_published' => (bool) ($base['is_published'] ?? true),
            'published_at' => $base['published_at'] ?? null,
        ];
    }

    /** @return array<string, mixed> */
    private function toAdminPayload(CareerContent $c): array
    {
        return [
            'id' => $c->id,
            'content_type' => $c->content_type,
            'category' => $c->category,
            'title' => $c->title,
            'subtitle' => $c->subtitle,
            'body' => $c->body,
            'question' => $c->question,
            'answer' => $c->answer,
            'rating_hint' => $c->rating_hint !== null ? (float) $c->rating_hint : null,
            'sort_order' => (int) $c->sort_order,
            'is_published' => (bool) $c->is_published,
            'published_at' => $c->published_at?->toIso8601String(),
            'helpful_count' => (int) $c->helpful_count,
            'created_at' => $c->created_at?->toIso8601String(),
            'updated_at' => $c->updated_at?->toIso8601String(),
        ];
    }
}
