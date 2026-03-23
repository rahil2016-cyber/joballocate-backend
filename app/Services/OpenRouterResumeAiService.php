<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenRouterResumeAiService
{
    public function improveSection(
        string $sectionName,
        ?string $currentText,
        ?string $instruction,
        ?string $jobContext,
    ): string {
        $apiKey = config('resume_ai.api_key');
        if ($apiKey === null || $apiKey === '') {
            throw new RuntimeException('Resume AI is not configured (MODEL_KEY missing).');
        }

        $model = config('resume_ai.model', 'arcee-ai/trinity-large-preview:free');
        $url = (string) config('resume_ai.chat_completions_url', 'https://openrouter.ai/api/v1/chat/completions');

        $system = <<<'SYS'
You are an expert resume and career coach. Improve the user's resume section for clarity, measurable impact, and ATS-friendly wording. Keep facts truthful: do not invent employers, dates, degrees, or certifications that are not implied by the user's text. Preserve the same language as the user's draft (e.g. English). Output only the improved section text — no preamble, no "Here is", no markdown fences unless the section truly needs a simple bullet list.
SYS;

        $userParts = [
            "Resume section: {$sectionName}",
        ];
        if ($currentText !== null && trim($currentText) !== '') {
            $userParts[] = "Current draft:\n".trim($currentText);
        } else {
            $userParts[] = 'The section is empty — write a strong first draft from the job seeker profile hints and instructions below.';
        }
        if ($instruction !== null && trim($instruction) !== '') {
            $userParts[] = 'Instructions from the user: '.trim($instruction);
        }
        if ($jobContext !== null && trim($jobContext) !== '') {
            $userParts[] = "Optional target role / job context:\n".trim($jobContext);
        }
        $userMessage = implode("\n\n", $userParts);

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                // OpenRouter expects these (see https://openrouter.ai/docs)
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => (string) config('app.name', 'JobAllocate').' Resume AI',
            ])
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.55,
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $detail = is_array($body)
                ? (data_get($body, 'error.message') ?? data_get($body, 'error') ?? $response->body())
                : $response->body();
            Log::warning('OpenRouter resume AI error', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            $hint = $response->status() === 404
                ? ' Check OPENROUTER_CHAT_COMPLETIONS_URL is https://openrouter.ai/api/v1/chat/completions and OPENROUTER_MODEL exists on OpenRouter.'
                : '';
            throw new RuntimeException(
                'OpenRouter error '.$response->status().': '.(is_string($detail) ? $detail : json_encode($detail)).$hint
            );
        }

        $text = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($text) || trim($text) === '') {
            Log::warning('OpenRouter resume AI empty content', ['json' => $response->json()]);
            throw new RuntimeException('AI returned empty text.');
        }

        return trim($text);
    }
}
