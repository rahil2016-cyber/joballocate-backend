<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('state', 120)->nullable()->after('location');
            $table->string('district', 120)->nullable()->after('state');
            $table->string('city', 120)->nullable()->after('district');
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->string('state', 120)->nullable()->after('country');
            $table->string('district', 120)->nullable()->after('state');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['state', 'district', 'city']);
        });

        Schema::table('job_seeker_profiles', function (Blueprint $table) {
            $table->dropColumn(['state', 'district']);
        });
    }
};

