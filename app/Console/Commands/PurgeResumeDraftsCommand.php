<?php

namespace App\Console\Commands;

use App\Models\JobSeekerProfile;
use App\Models\ResumeDraft;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Removes legacy resume drafts (JSON without schema "resume_model_v1") or all drafts.
 *
 * New Flutter builder saves payloads shaped like:
 * { "schema": "resume_model_v1", "version": 1, "data": { ... } }
 */
class PurgeResumeDraftsCommand extends Command
{
    protected $signature = 'resume:purge
                            {--all : Delete every resume draft for all users}
                            {--force : With --all, skip the confirmation prompt}
                            {--dry-run : Show counts only; do not delete}';

    protected $description = 'Delete legacy resume drafts (non resume_model_v1) or optionally wipe all drafts';

    public function handle(): int
    {
        $all = (bool) $this->option('all');
        $dry = (bool) $this->option('dry-run');

        if ($all) {
            return $this->purgeAll($dry);
        }

        return $this->purgeLegacy($dry);
    }

    private function purgeAll(bool $dry): int
    {
        $count = ResumeDraft::query()->count();

        if ($count === 0) {
            $this->info('No resume drafts in the database.');

            return self::SUCCESS;
        }

        if ($dry) {
            $this->warn("[dry-run] Would delete {$count} draft(s) and clear primary_resume_draft_id on all job seeker profiles.");

            return self::SUCCESS;
        }

        if (! (bool) $this->option('force')) {
            if (! $this->confirm("Delete ALL {$count} resume draft(s) for every user? This cannot be undone.", false)) {
                $this->info('Aborted.');

                return self::FAILURE;
            }
        }

        DB::transaction(function () {
            JobSeekerProfile::query()->update(['primary_resume_draft_id' => null]);
            ResumeDraft::query()->delete();
        });

        $this->info("Deleted {$count} resume draft(s) and cleared primary resume pointers.");

        return self::SUCCESS;
    }

    private function purgeLegacy(bool $dry): int
    {
        $query = ResumeDraft::query()->where(function ($q) {
            $q->whereNull('content->schema')
                ->orWhere('content->schema', '<>', 'resume_model_v1');
        });

        $count = (clone $query)->count();

        if ($count === 0) {
            $this->info('No legacy resume drafts found (all rows use schema resume_model_v1).');

            return self::SUCCESS;
        }

        if ($dry) {
            $this->warn("[dry-run] Would delete {$count} legacy draft(s) (schema missing or not resume_model_v1).");

            return self::SUCCESS;
        }

        $ids = $query->pluck('id');

        DB::transaction(function () use ($ids) {
            JobSeekerProfile::query()
                ->whereIn('primary_resume_draft_id', $ids)
                ->update(['primary_resume_draft_id' => null]);

            ResumeDraft::query()->whereIn('id', $ids)->delete();
        });

        $this->info("Deleted {$count} legacy resume draft(s).");

        return self::SUCCESS;
    }
}
