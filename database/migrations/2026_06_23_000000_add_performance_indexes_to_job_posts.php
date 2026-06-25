<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->index('industry_type');
            $table->index('employment_type');
            $table->index('experience_level');
            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropIndex(['industry_type']);
            $table->dropIndex(['employment_type']);
            $table->dropIndex(['experience_level']);
            $table->dropIndex(['status', 'published_at']);
        });
    }
};
