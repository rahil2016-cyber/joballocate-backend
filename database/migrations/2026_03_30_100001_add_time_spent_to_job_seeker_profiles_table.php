<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('total_time_spent_seconds')->default(0)->after('resume_credits_expires_at');
            $table->timestamp('last_app_activity_at')->nullable()->after('total_time_spent_seconds');
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn(['total_time_spent_seconds', 'last_app_activity_at']);
        });
    }
};
