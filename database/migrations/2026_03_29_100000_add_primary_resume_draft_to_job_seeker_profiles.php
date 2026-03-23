<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->foreignId('primary_resume_draft_id')
                ->nullable()
                ->after('resume_url')
                ->constrained('resume_drafts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_resume_draft_id');
        });
    }
};
