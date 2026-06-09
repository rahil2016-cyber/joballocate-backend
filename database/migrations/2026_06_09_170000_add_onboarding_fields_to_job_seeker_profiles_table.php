<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->boolean('onboarded')->default(false);
            $table->json('job_roles')->nullable();
            $table->boolean('is_experienced')->nullable();
            $table->string('current_company')->nullable();
            $table->string('current_role')->nullable();
            $table->json('preferred_locations')->nullable();
            $table->boolean('willing_to_relocate')->nullable();
            $table->json('employment_preferences')->nullable();
            $table->unsignedInteger('expected_salary')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'onboarded',
                'job_roles',
                'is_experienced',
                'current_company',
                'current_role',
                'preferred_locations',
                'willing_to_relocate',
                'employment_preferences',
                'expected_salary',
            ]);
        });
    }
};
