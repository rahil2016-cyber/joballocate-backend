<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/** AI career roadmap + interview prep using the same OpenRouter config as resume AI. */
class OpenRouterCareerCoachService
{
    public function careerPath(string $profileSummary): string
    {
        $system = <<<'SYS'
You are a senior career coach for job seekers in India. Based on the profile summary, produce a concise, practical 12–18 month career roadmap: suggested roles to target, 4–6 milestone steps, skills to build (with how), and one paragraph on networking or certifications if relevant. Use clear headings and bullet points. No fluff, no "As an AI". Keep it truthful — do not invent employers or degrees not implied by the profile.
SYS;

        return $this->complete($system, "Job seeker profile summary:\n\n".$profileSummary);
    }

    public function interviewPrep(string $profileSummary, ?string $focus): string
    {
        $system = <<<'SYS'
You are an interview coach. Give actionable interview preparation: likely questions for their background, STAR-style answer outlines (without fabricating fake stories), questions to ask employers, and a short same-day checklist. If a focus area is given, weight it heavily. Plain text with headings and bullets. No "As an AI".
SYS;

        $user = "Profile summary:\n".$profileSummary;
        if ($focus !== null && trim($focus) !== '') {
            $user .= "\n\nFocus / target role or company type:\n".trim($focus);
        }

        return $this->complete($system, $user);
    }

    private function complete(string $system, string $userMessage): string
    {
        $apiKey = config('resume_ai.api_key');
        if ($apiKey === null || $apiKey === '') {
            throw new RuntimeException('Career AI is not configured (MODEL_KEY missing).');
        }

        $model = config('resume_ai.model', 'arcee-ai/trinity-large-preview:free');
        $url = (string) config('resume_ai.chat_completions_url', 'https://openrouter.ai/api/v1/chat/completions');

        $response = Http::timeout(120)
            ->withHeaders([
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => (string) config('app.name', 'JobAllocate').' Career Coach',
            ])
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $userMessage],
                ],
                'temperature' => 0.5,
            ]);

        if (! $response->successful()) {
            $body = $response->json();
            $detail = is_array($body)
                ? (data_get($body, 'error.message') ?? data_get($body, 'error') ?? $response->body())
                : $response->body();
            Log::warning('OpenRouter career coach error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException(
                'OpenRouter error '.$response->status().': '.(is_string($detail) ? $detail : json_encode($detail))
            );
        }

        $text = data_get($response->json(), 'choices.0.message.content');
        if (! is_string($text) || trim($text) === '') {
            throw new RuntimeException('AI returned empty text.');
        }

        return trim($text);
    }
}
