<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeker_package_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seeker_package_id')->nullable()->constrained('seeker_packages')->nullOnDelete();
            $table->string('package_key', 64);
            $table->string('title', 120);
            $table->string('kind', 32);
            $table->unsignedInteger('price_inr')->default(0);
            $table->unsignedInteger('duration_days')->default(0);
            $table->unsignedInteger('applications_granted')->default(0);
            $table->unsignedInteger('resume_builds_granted')->default(0);
            $table->dateTime('activated_at');
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['user_id', 'activated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeker_package_purchases');
    }
};
