<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\CareerContent;
use App\Models\CareerContentHelpfulVote;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SeekerCareerContentController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in([
                CareerContent::TYPE_CAREER_GUIDANCE,
                CareerContent::TYPE_INTERVIEW_EXPERIENCE,
                CareerContent::TYPE_INTERVIEW_QA,
            ])],
        ]);

        $userId = $request->user()->id;
        $type = $validated['type'];

        $q = CareerContent::query()
            ->where('content_type', $type)
            ->where('is_published', true)
            ->where(function ($q): void {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id');

        $rows = $q->get();

        if ($type === CareerContent::TYPE_INTERVIEW_QA) {
            $grouped = $rows->groupBy(fn (CareerContent $c) => $c->category ?? 'General');
            $categories = [];
            foreach ($grouped as $cat => $items) {
                $categories[] = [
                    'category' => $cat,
                    'items' => $items->map(fn (CareerContent $c) => $this->qaItem($c, $userId))->values()->all(),
                ];
            }

            return $this->ok(['categories' => $categories], 'OK');
        }

        $items = $rows->map(fn (CareerContent $c) => $this->articleItem($c, $userId))->values()->all();

        return $this->ok(['items' => $items], 'OK');
    }

    public function setHelpful(Request $request, CareerContent $careerContent): JsonResponse
    {
        if (! $careerContent->is_published) {
            return $this->fail('Content not found.', null, 404);
        }

        $validated = $request->validate([
            'helpful' => ['required', 'boolean'],
        ]);

        $user = $request->user();
        $helpful = $validated['helpful'];

        try {
            DB::transaction(function () use ($user, $careerContent, $helpful): void {
                $careerContent = CareerContent::query()
                    ->whereKey($careerContent->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $exists = CareerContentHelpfulVote::query()
                    ->where('user_id', $user->id)
                    ->where('career_content_id', $careerContent->id)
                    ->lockForUpdate()
                    ->exists();

                if ($helpful) {
                    if (! $exists) {
                        CareerContentHelpfulVote::query()->create([
                            'user_id' => $user->id,
                            'career_content_id' => $careerContent->id,
                        ]);
                        $careerContent->increment('helpful_count');
                    }
                } elseif ($exists) {
                    CareerContentHelpfulVote::query()
                        ->where('user_id', $user->id)
                        ->where('career_content_id', $careerContent->id)
                        ->delete();
                    $careerContent->decrement('helpful_count');
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Could not update helpful vote.', null, 503);
        }

        $careerContent->refresh();

        return $this->ok([
            'helpful_count' => (int) $careerContent->helpful_count,
            'user_marked_helpful' => CareerContentHelpfulVote::query()
                ->where('user_id', $user->id)
                ->where('career_content_id', $careerContent->id)
                ->exists(),
        ], 'OK');
    }

    /** @return array<string, mixed> */
    private function articleItem(CareerContent $c, int $userId): array
    {
        return [
            'id' => $c->id,
            'title' => $c->title,
            'subtitle' => $c->subtitle,
            'body' => $c->body,
            'rating_hint' => $c->rating_hint !== null ? (float) $c->rating_hint : null,
            'helpful_count' => (int) $c->helpful_count,
            'user_marked_helpful' => CareerContentHelpfulVote::query()
                ->where('user_id', $userId)
                ->where('career_content_id', $c->id)
                ->exists(),
        ];
    }

    /** @return array<string, mixed> */
    private function qaItem(CareerContent $c, int $userId): array
    {
        return [
            'id' => $c->id,
            'question' => $c->question,
            'answer' => $c->answer,
            'helpful_count' => (int) $c->helpful_count,
            'user_marked_helpful' => CareerContentHelpfulVote::query()
                ->where('user_id', $userId)
                ->where('career_content_id', $c->id)
                ->exists(),
        ];
    }
}
