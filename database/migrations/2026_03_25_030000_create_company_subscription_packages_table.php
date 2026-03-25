<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscription_packages', function (Blueprint $table) {
            $table->id();

            $table->string('title', 160);
            $table->unsignedInteger('monthly_price_inr')->default(399);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscription_packages');
    }
};

