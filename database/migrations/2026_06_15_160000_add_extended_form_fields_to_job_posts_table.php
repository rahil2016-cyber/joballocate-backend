<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('assets_required', 500)->nullable();
            $table->string('languages', 500)->nullable();
            $table->string('incentive_detail', 500)->nullable();
            $table->string('job_timings', 500)->nullable();
            $table->string('working_days', 500)->nullable();
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->string('gender_preference', 64)->nullable();
            $table->string('contact_preference', 64)->default('phone_call');
            $table->string('contact_person', 200)->nullable();
            $table->string('contact_phone', 64)->nullable();
            $table->string('contact_email', 200)->nullable();
            $table->string('department', 200)->nullable();
            $table->string('role', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn([
                'assets_required',
                'languages',
                'incentive_detail',
                'job_timings',
                'working_days',
                'age_min',
                'age_max',
                'gender_preference',
                'contact_preference',
                'contact_person',
                'contact_phone',
                'contact_email',
                'department',
                'role',
            ]);
        });
    }
};
