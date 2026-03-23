<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Services\OpenRouterResumeAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ResumeAiController extends Controller
{
    use ApiResponses;

    /**
     * Improve one resume section via OpenRouter. Consumes one resume build credit when successful.
     */
    public function assist(Request $request, OpenRouterResumeAiService $ai): JsonResponse
    {
        $validated = $request->validate([
            'section_name' => ['required', 'string', 'max:120'],
            'current_text' => ['nullable', 'string', 'max:20000'],
            'instruction' => ['nullable', 'string', 'max:2000'],
            'job_context' => ['nullable', 'string', 'max:8000'],
        ]);

        $user = $request->user();

        try {
            $improved = DB::transaction(function () use ($user, $validated, $ai) {
                /** @var JobSeekerProfile|null $profile */
                $profile = JobSeekerProfile::query()
                    ->where('user_id', $user->id)
                    ->lockForUpdate()
                    ->first();

                if (! $profile) {
                    throw new \DomainException('Create your job seeker profile first.');
                }

                if (! $profile->canBuildResume()) {
                    throw new \DomainException(
                        'No resume build credits left. Choose a plan that includes resume credits in Plans & packages.'
                    );
                }

                $text = $ai->improveSection(
                    $validated['section_name'],
                    $validated['current_text'] ?? null,
                    $validated['instruction'] ?? null,
                    $validated['job_context'] ?? null,
                );

                $profile->resume_builds_remaining = max(0, (int) $profile->resume_builds_remaining - 1);
                $profile->save();

                return $text;
            });
        } catch (\DomainException $e) {
            $msg = $e->getMessage();
            $status = str_contains($msg, 'credits') ? 402 : 422;

            return $this->fail($msg, null, $status);
        } catch (RuntimeException $e) {
            return $this->fail($e->getMessage(), null, 503);
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Could not generate resume text. Try again later.', null, 503);
        }

        return $this->ok([
            'improved_text' => $improved,
        ], 'OK');
    }
}
