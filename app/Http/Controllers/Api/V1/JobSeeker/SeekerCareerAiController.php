<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Services\OpenRouterCareerCoachService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class SeekerCareerAiController extends Controller
{
    use ApiResponses;

    public function coach(Request $request, OpenRouterCareerCoachService $coach): JsonResponse
    {
        $validated = $request->validate([
            'kind' => ['required', Rule::in(['career_path', 'interview_prep'])],
            'focus' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = $request->user();
        $profile = JobSeekerProfile::query()->where('user_id', $user->id)->first();

        $lines = [
            'Name: '.$user->name,
            'Email: '.($user->email ?? ''),
            'Phone: '.($user->phone ?? ''),
        ];
        if ($profile) {
            $lines[] = 'Headline: '.(string) ($profile->headline ?? '');
            $lines[] = 'Bio: '.(string) ($profile->bio ?? '');
            $lines[] = 'Skills: '.json_encode($profile->skills ?? []);
            $lines[] = 'Industry preference: '.(string) ($profile->industry_type ?? '');
            $lines[] = 'Experience years: '.(string) ($profile->experience_years ?? '');
            $lines[] = 'Location: '.implode(', ', array_filter([
                $profile->city,
                $profile->district,
                $profile->state,
                $profile->country,
            ]));
        }
        $summary = implode("\n", $lines);

        try {
            $text = match ($validated['kind']) {
                'career_path' => $coach->careerPath($summary),
                'interview_prep' => $coach->interviewPrep($summary, $validated['focus'] ?? null),
            };
        } catch (\DomainException $e) {
            return $this->fail($e->getMessage(), null, 422);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 503);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Could not generate advice. Try again later.', null, 503);
        }

        return $this->ok([
            'text' => $text,
            'kind' => $validated['kind'],
        ], 'OK');
    }
}
