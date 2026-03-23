<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Restricts DB columns to allowed enum values (phpMyAdmin / Workbench show ENUM = dropdown).
 * MySQL only; SQLite keeps string columns.
 */
return new class extends Migration
{
    private const COMPANY_STATUSES = ['unverified', 'pending', 'verified', 'rejected'];

    private const JOB_STATUSES = ['draft', 'pending_review', 'published', 'closed', 'rejected'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $this->normalizeCompanies();
        $this->normalizeJobPosts();

        $companyEnum = implode("','", self::COMPANY_STATUSES);
        DB::statement("ALTER TABLE companies MODIFY verification_status ENUM('{$companyEnum}') NOT NULL DEFAULT 'unverified'");

        $jobEnum = implode("','", self::JOB_STATUSES);
        DB::statement("ALTER TABLE job_posts MODIFY status ENUM('{$jobEnum}') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE companies MODIFY verification_status VARCHAR(32) NOT NULL DEFAULT 'unverified'");
        DB::statement("ALTER TABLE job_posts MODIFY status VARCHAR(32) NOT NULL DEFAULT 'draft'");
    }

    private function normalizeCompanies(): void
    {
        $allowed = self::COMPANY_STATUSES;
        DB::table('companies')
            ->whereNotIn('verification_status', $allowed)
            ->update(['verification_status' => 'unverified']);
    }

    private function normalizeJobPosts(): void
    {
        $allowed = self::JOB_STATUSES;
        DB::table('job_posts')
            ->whereNotIn('status', $allowed)
            ->update(['status' => 'draft']);
    }
};
