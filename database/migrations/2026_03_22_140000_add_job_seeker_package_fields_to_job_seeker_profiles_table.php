<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->string('package_key', 32)->nullable()->after('resume_url');
            $table->unsignedInteger('applications_remaining')->nullable()->after('package_key');
            $table->timestamp('package_activated_at')->nullable()->after('applications_remaining');
            $table->timestamp('package_expires_at')->nullable()->after('package_activated_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'package_key',
                'applications_remaining',
                'package_activated_at',
                'package_expires_at',
            ]);
        });
    }
};
