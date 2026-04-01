<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('message')->nullable();
            $table->text('admin_reply')->nullable();
            $table->foreignId('admin_reply_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('admin_replied_at')->nullable();
            $table->unsignedTinyInteger('admin_quality_rating')->nullable();
            $table->timestamps();

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_feedback');
    }
};
