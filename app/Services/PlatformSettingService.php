<?php

namespace App\Services;

use App\Models\PlatformSetting;
use Illuminate\Database\QueryException;

final class PlatformSettingService
{
    public function moderationSettings(): array
    {
        return [
            'auto_verify_new_companies' => $this->bool(
                'moderation.auto_verify_new_companies',
                (bool) config('joballocate.auto_verify_new_companies', true)
            ),
            'auto_publish_new_jobs' => $this->bool(
                'moderation.auto_publish_new_jobs',
                (bool) config('joballocate.auto_publish_new_jobs', true)
            ),
        ];
    }

    public function autoVerifyCompanies(): bool
    {
        return $this->moderationSettings()['auto_verify_new_companies'];
    }

    public function autoPublishJobs(): bool
    {
        return $this->moderationSettings()['auto_publish_new_jobs'];
    }

    public function updateModerationSettings(array $data, ?int $updatedBy = null): array
    {
        foreach ($data as $key => $value) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => "moderation.{$key}"],
                [
                    'value' => ['enabled' => (bool) $value],
                    'updated_by' => $updatedBy,
                ]
            );
        }

        return $this->moderationSettings();
    }

    /**
     * @return array{
     *   job_seeker_enabled: bool,
     *   company_enabled: bool,
     *   job_seeker_benefits_text: string,
     *   company_benefits_text: string,
     *   app_download_url: string
     * }
     */
    public function referEarnSettings(): array
    {
        return [
            'job_seeker_enabled' => $this->bool('refer_earn.job_seeker_enabled', true),
            'company_enabled' => $this->bool('refer_earn.company_enabled', true),
            'job_seeker_benefits_text' => $this->string('refer_earn.job_seeker_benefits_text', 'Invite friends and earn rewards on packages and resume benefits.'),
            'company_benefits_text' => $this->string('refer_earn.company_benefits_text', 'Refer other employers and get subscription discounts when they join.'),
            'app_download_url' => $this->string('refer_earn.app_download_url', ''),
        ];
    }

    /**
     * @param  array{
     *   job_seeker_enabled?: bool,
     *   company_enabled?: bool,
     *   job_seeker_benefits_text?: string,
     *   company_benefits_text?: string,
     *   app_download_url?: string|null
     * }  $data
     */
    public function updateReferEarnSettings(array $data, ?int $updatedBy = null): array
    {
        $map = [
            'job_seeker_enabled' => 'refer_earn.job_seeker_enabled',
            'company_enabled' => 'refer_earn.company_enabled',
            'job_seeker_benefits_text' => 'refer_earn.job_seeker_benefits_text',
            'company_benefits_text' => 'refer_earn.company_benefits_text',
            'app_download_url' => 'refer_earn.app_download_url',
        ];

        foreach ($map as $field => $key) {
            if (! array_key_exists($field, $data)) {
                continue;
            }
            $val = $data[$field];
            if (str_ends_with($field, '_enabled')) {
                PlatformSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => ['enabled' => (bool) $val], 'updated_by' => $updatedBy]
                );
            } else {
                PlatformSetting::query()->updateOrCreate(
                    ['key' => $key],
                    ['value' => ['text' => (string) ($val ?? '')], 'updated_by' => $updatedBy]
                );
            }
        }

        return $this->referEarnSettings();
    }

    private function string(string $key, string $default): string
    {
        try {
            $row = PlatformSetting::query()->where('key', $key)->first();
        } catch (\Illuminate\Database\QueryException) {
            return $default;
        }
        if (! $row || ! is_array($row->value)) {
            return $default;
        }

        return (string) ($row->value['text'] ?? $default);
    }

    private function bool(string $key, bool $default): bool
    {
        try {
            $row = PlatformSetting::query()->where('key', $key)->first();
        } catch (QueryException) {
            // If migration hasn't run yet, fall back to config defaults.
            return $default;
        }
        if (! $row || ! is_array($row->value)) {
            return $default;
        }

        return (bool) ($row->value['enabled'] ?? $default);
    }
}

