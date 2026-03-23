<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->json('education')->nullable()->after('skills');
            $table->string('industry_type', 64)->nullable()->after('country');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->string('industry_type', 64)->nullable()->after('industry');
        });

        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('industry_type', 64)->nullable()->after('experience_level');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn('industry_type');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('industry_type');
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn(['education', 'industry_type']);
        });
    }
};
