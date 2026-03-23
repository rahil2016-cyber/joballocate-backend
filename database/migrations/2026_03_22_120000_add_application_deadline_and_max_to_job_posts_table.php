<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->timestamp('application_deadline_at')->nullable()->after('published_at');
            $table->unsignedInteger('max_applications')->nullable()->after('application_deadline_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['application_deadline_at', 'max_applications']);
        });
    }
};
