<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_packages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('title', 120);
            $table->text('description')->nullable();
            /** job_applications | resume | combo */
            $table->string('kind', 32);
            $table->unsignedInteger('price_inr')->default(0);
            $table->unsignedInteger('duration_days')->default(30);
            $table->unsignedInteger('applications_included')->default(0);
            $table->unsignedInteger('resume_builds_included')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_packages');
    }
};
