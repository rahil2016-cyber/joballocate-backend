<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\SeekerFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeekerFeedbackController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $perPage = (int) ($validated['per_page'] ?? 20);

        $rows = SeekerFeedback::query()
            ->where('user_id', $user->id)
            ->orderByDesc('id')
            ->paginate($perPage);

        $data = collect($rows->items())->map(fn (SeekerFeedback $f) => $this->toSeekerPayload($f))->values()->all();

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
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $row = SeekerFeedback::query()->create([
            'user_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'message' => $validated['message'] ?? null,
        ]);

        return $this->ok($this->toSeekerPayload($row->fresh()), 'Thanks — your feedback was submitted.');
    }

    /** @return array<string, mixed> */
    private function toSeekerPayload(SeekerFeedback $f): array
    {
        return [
            'id' => $f->id,
            'rating' => (int) $f->rating,
            'message' => $f->message,
            'admin_reply' => $f->admin_reply,
            'admin_replied_at' => $f->admin_replied_at?->toIso8601String(),
            'created_at' => $f->created_at?->toIso8601String(),
        ];
    }
}
