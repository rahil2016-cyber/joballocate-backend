<?php

namespace App\Support;

use App\Models\Company;
use App\Models\JobSeekerProfile;
use App\Models\User;

class ProfileCompletion
{
    /**
     * Job seeker profile completeness (0–100). Ten weighted checks.
     */
    public static function seekerPercent(User $user, JobSeekerProfile $p): int
    {
        $percent = 0;

        // 1. Basic Info (Name) = 15%
        if (filled($user->name) && $user->name !== 'User') {
            $percent += 15;
        }

        // 2. Job Interests (job_roles) = 20%
        if (is_array($p->job_roles) && count($p->job_roles) > 0) {
            $percent += 20;
        }

        // 3. Skills (skills) = 20%
        if (is_array($p->skills) && count($p->skills) > 0) {
            $percent += 20;
        }

        // 4. Education = 10%
        $edu = $p->education;
        if (is_array($edu)) {
            foreach ($edu as $row) {
                if (is_array($row) && (filled($row['title'] ?? null) || filled($row['institution'] ?? null))) {
                    $percent += 10;
                    break;
                }
            }
        }

        // 5. Experience / Status = 15%
        if ($p->is_experienced !== null || filled($p->current_status)) {
            $percent += 15;
        }

        // 6. Location = 10%
        if (filled($p->city) || (is_array($p->preferred_locations) && count($p->preferred_locations) > 0)) {
            $percent += 10;
        }

        // 7. Resume = 10%
        if (filled($p->resume_url)) {
            $percent += 10;
        }

        return min($percent, 100);
    }

    /**
     * Company profile completeness (0–100). Ten weighted checks.
     */
    public static function companyPercent(User $user, Company $c): int
    {
        $checks = 0;
        $total = 10;

        if (filled($c->industry) || filled($c->industry_type)) {
            $checks++;
        }
        if (filled($c->website)) {
            $checks++;
        }
        if (filled($c->description) || filled($c->company_bio)) {
            $checks++;
        }
        if (filled($c->location)) {
            $checks++;
        }
        if ($c->established_year !== null && $c->established_year >= 1800 && $c->established_year <= (int) date('Y')) {
            $checks++;
        }
        if (filled($c->what_we_do)) {
            $checks++;
        }
        $team = $c->team_members;
        if (is_array($team)) {
            foreach ($team as $m) {
                if (is_array($m) && filled($m['name'] ?? null)) {
                    $checks++;
                    break;
                }
            }
        }
        if (filled($c->gst_number)) {
            $checks++;
        }
        if (filled($c->logo_url)) {
            $checks++;
        }
        if (filled($user->phone)) {
            $checks++;
        }

        return (int) round($checks / $total * 100);
    }
}
