<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('gst_number', 32)->nullable()->after('description');
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('country');
        });

        Schema::create('resume_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('template_id', 64);
            $table->json('content');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_drafts');

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn('date_of_birth');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('gst_number');
        });
    }
};
