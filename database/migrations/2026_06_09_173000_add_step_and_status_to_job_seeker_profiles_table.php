<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->unsignedTinyInteger('onboarding_step')->default(1);
            $table->string('current_status', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn(['onboarding_step', 'current_status']);
        });
    }
};
