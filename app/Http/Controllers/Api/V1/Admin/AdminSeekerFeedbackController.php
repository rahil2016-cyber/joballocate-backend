<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\SeekerFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminSeekerFeedbackController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'replied' => ['nullable', Rule::in(['0', '1', 'yes', 'no'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $q = SeekerFeedback::query()
            ->with(['user:id,name,email,phone', 'adminReplier:id,name,email'])
            ->orderByDesc('id');

        if (! empty($validated['search'] ?? null)) {
            $term = '%'.$validated['search'].'%';
            $q->where(function ($query) use ($term): void {
                $query->where('message', 'like', $term)
                    ->orWhere('admin_reply', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term): void {
                        $uq->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        }

        $replied = $validated['replied'] ?? null;
        if ($replied === '1' || $replied === 'yes') {
            $q->whereNotNull('admin_reply');
        } elseif ($replied === '0' || $replied === 'no') {
            $q->whereNull('admin_reply');
        }

        $rows = $q->paginate((int) ($validated['per_page'] ?? 25));
        $data = collect($rows->items())->map(fn (SeekerFeedback $f) => $this->toPayload($f))->values()->all();

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

    public function update(Request $request, SeekerFeedback $seekerFeedback): JsonResponse
    {
        $validated = $request->validate([
            'admin_reply' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'admin_quality_rating' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $admin = $request->user();

        if (array_key_exists('admin_reply', $validated)) {
            $reply = $validated['admin_reply'];
            $seekerFeedback->admin_reply = $reply;
            if ($reply !== null && trim((string) $reply) !== '') {
                $seekerFeedback->admin_reply_user_id = $admin->id;
                $seekerFeedback->admin_replied_at = now();
            } else {
                $seekerFeedback->admin_reply_user_id = null;
                $seekerFeedback->admin_replied_at = null;
            }
        }

        if (array_key_exists('admin_quality_rating', $validated)) {
            $seekerFeedback->admin_quality_rating = $validated['admin_quality_rating'];
        }

        $seekerFeedback->save();

        return $this->ok($this->toPayload($seekerFeedback->fresh(['user', 'adminReplier'])), 'Updated');
    }

    /** @return array<string, mixed> */
    private function toPayload(SeekerFeedback $f): array
    {
        return [
            'id' => $f->id,
            'rating' => (int) $f->rating,
            'message' => $f->message,
            'admin_reply' => $f->admin_reply,
            'admin_quality_rating' => $f->admin_quality_rating !== null ? (int) $f->admin_quality_rating : null,
            'admin_replied_at' => $f->admin_replied_at?->toIso8601String(),
            'admin_replier' => $f->adminReplier ? [
                'id' => $f->adminReplier->id,
                'name' => $f->adminReplier->name,
                'email' => $f->adminReplier->email,
            ] : null,
            'user' => $f->user ? [
                'id' => $f->user->id,
                'name' => $f->user->name,
                'email' => $f->user->email,
                'phone' => $f->user->phone,
            ] : null,
            'created_at' => $f->created_at?->toIso8601String(),
        ];
    }
}
