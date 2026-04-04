<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Enums\ApplicationStatus;
use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminApplicationController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->get('per_page', 25)));

        $q = Application::query()
            ->with([
                'jobPost:id,title,company_id',
                'jobPost.company:id,name',
                'user:id,name,email,phone',
            ])
            ->latest('applied_at');

        if ($request->filled('search')) {
            $term = '%'.$request->get('search').'%';
            $q->where(function ($qq) use ($term): void {
                $qq->whereHas('user', fn ($u) => $u->where('name', 'like', $term)->orWhere('email', 'like', $term))
                    ->orWhereHas('jobPost', fn ($j) => $j->where('title', 'like', $term));
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status');
            $allowed = array_map(fn (ApplicationStatus $s) => $s->value, ApplicationStatus::cases());
            if (in_array($status, $allowed, true)) {
                $q->where('status', $status);
            }
        }

        $paginator = $q->paginate($perPage);

        $rows = collect($paginator->items())
            ->map(fn (Application $a): array => $this->serializeApplication($a))
            ->values()
            ->all();

        return $this->ok(
            $rows,
            'OK',
            [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ]
        );
    }

    public function show(Application $application): JsonResponse
    {
        $application->load([
            'jobPost:id,title,company_id',
            'jobPost.company:id,name',
            'user:id,name,email,phone',
        ]);

        return $this->ok($this->serializeApplication($application), 'OK');
    }

    public function update(Request $request, Application $application): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ApplicationStatus::class)],
            'employer_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $application->status = $validated['status'];
        if (array_key_exists('employer_note', $validated)) {
            $application->employer_note = $validated['employer_note'];
        }
        $application->save();
        $application->refresh();
        $application->load([
            'jobPost:id,title,company_id',
            'jobPost.company:id,name',
            'user:id,name,email,phone',
        ]);

        return $this->ok($this->serializeApplication($application), 'Application updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeApplication(Application $a): array
    {
        $job = $a->jobPost;
        $u = $a->user;

        return [
            'id' => $a->id,
            'status' => $a->status instanceof \BackedEnum ? $a->status->value : $a->status,
            'applied_at' => $a->applied_at?->toIso8601String(),
            'cover_letter' => $a->cover_letter,
            'employer_note' => $a->employer_note,
            'job' => $job ? [
                'id' => $job->id,
                'title' => $job->title,
                'company_name' => $job->company?->name,
            ] : null,
            'seeker' => $u ? [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'phone' => $u->phone,
            ] : null,
        ];
    }
}
