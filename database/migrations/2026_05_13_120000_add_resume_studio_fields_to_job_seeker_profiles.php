<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('job_seeker_profiles', 'resume_document')) {
                $table->json('resume_document')->nullable()->after('achievements');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'hometown')) {
                $table->string('hometown', 160)->nullable()->after('resume_document');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'residing_in_india')) {
                $table->boolean('residing_in_india')->default(true)->after('hometown');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'highest_qualification')) {
                $table->string('highest_qualification', 64)->nullable()->after('residing_in_india');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'work_experience')) {
                $table->json('work_experience')->nullable()->after('highest_qualification');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'languages_known')) {
                $table->json('languages_known')->nullable()->after('work_experience');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'certifications_structured')) {
                $table->json('certifications_structured')->nullable()->after('languages_known');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'academic_achievements')) {
                $table->json('academic_achievements')->nullable()->after('certifications_structured');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'awards_honors')) {
                $table->json('awards_honors')->nullable()->after('academic_achievements');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'competitive_exam_results')) {
                $table->json('competitive_exam_results')->nullable()->after('awards_honors');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $cols = [
                'resume_document',
                'hometown',
                'residing_in_india',
                'highest_qualification',
                'work_experience',
                'languages_known',
                'certifications_structured',
                'academic_achievements',
                'awards_honors',
                'competitive_exam_results',
            ];
            $drop = [];
            foreach ($cols as $c) {
                if (Schema::hasColumn('job_seeker_profiles', $c)) {
                    $drop[] = $c;
                }
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
