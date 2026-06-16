<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->boolean('security_deposit')->default(false)->after('incentive_detail');
            $table->string('interview_timings', 500)->nullable()->after('job_timings');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['security_deposit', 'interview_timings']);
        });
    }
};
