<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_subscription_payments', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('pending')->after('is_free');
            $table->string('razorpay_order_id', 128)->nullable()->after('payment_status');
            $table->string('razorpay_payment_id', 128)->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature', 256)->nullable()->after('razorpay_payment_id');
            
            // Allow purchased_at to be nullable (as it will be set upon successful activation)
            $table->timestamp('purchased_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);
            $table->timestamp('purchased_at')->nullable(false)->change();
        });
    }
};
