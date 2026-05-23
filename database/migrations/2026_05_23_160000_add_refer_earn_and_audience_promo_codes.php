<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'referral_code')) {
                $table->string('referral_code', 16)->nullable()->unique()->after('is_active');
            }
        });

        Schema::create('audience_promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64);
            $table->string('audience', 32); // job_seeker | company
            $table->string('label', 120)->nullable();
            $table->text('benefit_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redemptions_count')->default(0);
            $table->timestamps();

            $table->unique(['code', 'audience']);
            $table->index(['audience', 'is_active']);
        });

        Schema::create('audience_promo_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('audience', 32);
            $table->string('code_used', 64);
            $table->foreignId('audience_promo_code_id')->nullable()->constrained('audience_promo_codes')->nullOnDelete();
            $table->foreignId('referrer_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audience_promo_redemptions');
        Schema::dropIfExists('audience_promo_codes');
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'referral_code')) {
                $table->dropColumn('referral_code');
            }
        });
    }
};
