<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->string('job_package_key', 64)->nullable()->after('package_key');
            $table->string('resume_package_key', 64)->nullable()->after('job_package_key');
            $table->dateTime('job_credits_expires_at')->nullable()->after('package_expires_at');
            $table->dateTime('resume_credits_expires_at')->nullable()->after('job_credits_expires_at');
        });

        foreach (DB::table('job_seeker_profiles')->whereNotNull('package_expires_at')->cursor() as $row) {
            DB::table('job_seeker_profiles')->where('id', $row->id)->update([
                'job_credits_expires_at' => $row->package_expires_at,
                'resume_credits_expires_at' => $row->package_expires_at,
                'job_package_key' => $row->package_key,
                'resume_package_key' => $row->package_key,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'job_package_key',
                'resume_package_key',
                'job_credits_expires_at',
                'resume_credits_expires_at',
            ]);
        });
    }
};
