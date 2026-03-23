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
        $checks = 0;
        $total = 12;

        if (filled($p->headline)) {
            $checks++;
        }
        if (filled($p->bio)) {
            $checks++;
        }
        if (is_array($p->skills) && count($p->skills) > 0) {
            $checks++;
        }
        if (filled($p->industry_type)) {
            $checks++;
        }
        $edu = $p->education;
        if (is_array($edu)) {
            foreach ($edu as $row) {
                if (is_array($row) && (filled($row['title'] ?? null) || filled($row['institution'] ?? null))) {
                    $checks++;
                    break;
                }
            }
        }
        if (filled($p->city)) {
            $checks++;
        }
        if (filled($p->country)) {
            $checks++;
        }
        if ($p->experience_years !== null) {
            $checks++;
        }
        if (filled($p->resume_url)) {
            $checks++;
        }
        if ($p->date_of_birth !== null) {
            $checks++;
        }
        if (filled($user->phone)) {
            $checks++;
        }
        if ($p->expected_salary_min !== null || $p->expected_salary_max !== null) {
            $checks++;
        }

        return (int) round($checks / $total * 100);
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
