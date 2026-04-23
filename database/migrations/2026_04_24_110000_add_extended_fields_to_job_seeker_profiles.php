<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('job_seeker_profiles', 'gender')) {
                $table->string('gender', 32)->nullable()->after('date_of_birth');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'portfolio_url')) {
                $table->string('portfolio_url', 500)->nullable()->after('industry_type');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'internships')) {
                $table->json('internships')->nullable()->after('education');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'projects')) {
                $table->json('projects')->nullable()->after('internships');
            }
            if (! Schema::hasColumn('job_seeker_profiles', 'achievements')) {
                $table->json('achievements')->nullable()->after('projects');
            }
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $drop = [];
            foreach (['gender', 'portfolio_url', 'internships', 'projects', 'achievements'] as $column) {
                if (Schema::hasColumn('job_seeker_profiles', $column)) {
                    $drop[] = $column;
                }
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
