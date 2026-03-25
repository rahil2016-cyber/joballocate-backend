<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_subscription_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('cycle_number'); // 1 = first month, 2 = next month, etc.

            $table->string('coupon_code_used', 64)->nullable();

            $table->unsignedInteger('amount_inr')->default(0); // free month = 0
            $table->boolean('is_free')->default(false);

            $table->timestamp('purchased_at');

            $table->unique(['company_id', 'cycle_number']);
            $table->index(['company_id', 'coupon_code_used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_subscription_payments');
    }
};

