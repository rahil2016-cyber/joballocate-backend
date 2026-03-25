<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();

            // Admin chooses whether the coupon is for a specific state or a specific district.
            // - state: match company.state
            // - district: match company.district
            $table->string('target_type', 16);
            $table->string('target_value', 120);

            $table->unsignedSmallInteger('discount_percent')->default(0); // for renewal payments
            $table->boolean('free_first_month')->default(true); // for cycle #1

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_coupons');
    }
};

