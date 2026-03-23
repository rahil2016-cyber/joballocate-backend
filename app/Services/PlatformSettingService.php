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

