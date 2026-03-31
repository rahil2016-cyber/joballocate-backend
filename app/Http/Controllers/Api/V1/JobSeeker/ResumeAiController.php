<?php

namespace App\Http\Controllers\Api\V1\JobSeeker;

use App\Http\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\JobSeekerProfile;
use App\Services\OpenRouterResumeAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ResumeAiController extends Controller
{
    use ApiResponses;

    /**
     * Improve one resume section via OpenRouter. Free for all seekers (no credits).
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
            JobSeekerProfile::query()->firstOrCreate(
                ['user_id' => $user->id],
                [],
            );

            $improved = $ai->improveSection(
                $validated['section_name'],
                $validated['current_text'] ?? null,
                $validated['instruction'] ?? null,
                $validated['job_context'] ?? null,
            );
        } catch (\DomainException $e) {
            return $this->fail($e->getMessage(), null, 422);
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
