<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Verifies OpenRouter URL, API key, and model (same path as resume AI).
 */
class OpenRouterPingCommand extends Command
{
    protected $signature = 'openrouter:ping';

    protected $description = 'Test OpenRouter chat/completions (MODEL_KEY + OPENROUTER_MODEL)';

    public function handle(): int
    {
        $key = config('resume_ai.api_key');
        $url = config('resume_ai.chat_completions_url');
        $model = config('resume_ai.model');

        if ($key === null || $key === '') {
            $this->error('MODEL_KEY is empty in .env. Run: php artisan config:clear');

            return self::FAILURE;
        }

        $this->line('URL: '.$url);
        $this->line('Model: '.$model);

        $response = Http::timeout(60)
            ->withHeaders([
                'Authorization' => 'Bearer '.$key,
                'Content-Type' => 'application/json',
                'HTTP-Referer' => config('app.url', 'http://localhost'),
                'X-Title' => (string) config('app.name', 'JobAllocate').' CLI ping',
            ])
            ->post($url, [
                'model' => $model,
                'messages' => [
                    ['role' => 'user', 'content' => 'Reply with exactly: OK'],
                ],
                'max_tokens' => 8,
            ]);

        if (! $response->successful()) {
            $this->error('HTTP '.$response->status());
            $this->line($response->body());
            $json = $response->json();
            $msg = is_array($json) ? data_get($json, 'error.message') : null;
            if (is_string($msg) &&
                (str_contains($msg, 'guardrail') || str_contains($msg, 'data policy'))) {
                $this->newLine();
                $this->warn('OpenRouter blocked this request due to your account privacy / data policy.');
                $this->line('Fix: open https://openrouter.ai/settings/privacy and allow model usage (or adjust training/data settings), then retry.');
            }

            return self::FAILURE;
        }

        $text = data_get($response->json(), 'choices.0.message.content');
        $this->info('Success. Reply: '.trim((string) $text));

        return self::SUCCESS;
    }
}
