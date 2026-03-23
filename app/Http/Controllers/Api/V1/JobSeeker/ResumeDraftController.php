<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Models\ResumeDraft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeDraftController extends Controller
{
    use ApiResponses;

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $drafts = ResumeDraft::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        $primaryId = $user->jobSeekerProfile?->primary_resume_draft_id;

        $rows = $drafts->map(function (ResumeDraft $d) use ($primaryId) {
            $a = $d->toArray();

            return array_merge($a, [
                'is_primary' => $primaryId !== null && (int) $primaryId === (int) $d->id,
            ]);
        })->values()->all();

        return $this->ok([
            'drafts' => $rows,
            'primary_resume_draft_id' => $primaryId,
        ]);
    }

    public function setPrimary(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'resume_draft_id' => ['required', 'integer', 'exists:resume_drafts,id'],
        ]);

        $draft = ResumeDraft::query()
            ->where('id', $validated['resume_draft_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $draft) {
            return $this->fail('Resume not found.', null, 404);
        }

        $profile = JobSeekerProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            []
        );

        $profile->primary_resume_draft_id = $draft->id;
        $profile->save();

        $profile->load('primaryResumeDraft');

        return $this->ok([
            'profile' => $profile,
            'primary_resume_draft' => $profile->primaryResumeDraft,
        ], 'This resume will be highlighted for employers when you apply.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'template_id' => ['required', 'string', 'max:64'],
            'content' => ['required', 'array'],
        ]);

        $draft = ResumeDraft::query()->create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'template_id' => $validated['template_id'],
            'content' => $validated['content'],
        ]);

        $profile = JobSeekerProfile::query()->firstOrCreate(
            ['user_id' => $request->user()->id],
            []
        );
        if ($profile->primary_resume_draft_id === null) {
            $profile->primary_resume_draft_id = $draft->id;
            $profile->save();
        }

        return $this->ok($draft->fresh(), 'Resume saved.', null, 201);
    }
}
