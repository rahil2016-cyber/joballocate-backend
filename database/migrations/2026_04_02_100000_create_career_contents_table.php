<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_contents', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 40); // career_guidance | interview_experience | interview_qa
            $table->string('category', 120)->nullable();
            $table->string('title', 255)->nullable();
            $table->string('subtitle', 255)->nullable();
            $table->longText('body')->nullable();
            $table->string('question', 500)->nullable();
            $table->longText('answer')->nullable();
            $table->decimal('rating_hint', 2, 1)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('helpful_count')->default(0);
            $table->timestamps();

            $table->index(['content_type', 'is_published', 'sort_order']);
            $table->index(['content_type', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_contents');
    }
};
