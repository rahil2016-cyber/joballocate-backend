<?php

namespace App\Services;

use App\Models\JobPost;

final class JobShareService
{
    public function __construct(
        private readonly PlatformSettingService $platformSettings
    ) {}

    /**
     * @return array{
     *   job_id: int,
     *   job_title: string,
     *   company_name: string,
     *   location: string|null,
     *   app_link: string,
     *   web_link: string|null,
     *   share_text: string,
     *   play_store_available: bool
     * }
     */
    public function payloadForJob(JobPost $job): array
    {
        $links = $this->platformSettings->appLinkSettings();
        $scheme = $links['deep_link_scheme'];
        $jobId = (int) $job->id;
        $appLink = "{$scheme}://job/{$jobId}";

        $webBase = trim((string) ($links['job_share_web_base_url'] ?? ''));
        $webLink = $webBase !== '' ? rtrim($webBase, '/').'/job/'.$jobId : null;

        $title = (string) $job->title;
        $company = (string) ($job->company?->name ?? 'Company');
        $location = $job->location ? (string) $job->location : null;

        $shareText = $this->buildShareText($title, $company, $location, $appLink, $webLink);

        $storeUrl = trim((string) ($links['app_download_url'] ?? ''));

        return [
            'job_id' => $jobId,
            'job_title' => $title,
            'company_name' => $company,
            'location' => $location,
            'app_link' => $appLink,
            'web_link' => $webLink,
            'share_text' => $shareText,
            'play_store_available' => $storeUrl !== '',
        ];
    }

    private function buildShareText(
        string $title,
        string $company,
        ?string $location,
        string $appLink,
        ?string $webLink
    ): string {
        $lines = [
            'Check out this job on JobAllocate!',
            '',
            $title,
            'at '.$company.($location ? ' · '.$location : ''),
            '',
            'Open in the JobAllocate app:',
            $appLink,
        ];

        if ($webLink) {
            $lines[] = '';
            $lines[] = 'Link: '.$webLink;
        }

        return implode("\n", $lines);
    }
}
