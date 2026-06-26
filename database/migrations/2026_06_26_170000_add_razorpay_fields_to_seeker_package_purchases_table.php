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
        Schema::table('seeker_package_purchases', function (Blueprint $table) {
            $table->string('payment_status', 32)->default('pending')->after('expires_at');
            $table->string('razorpay_order_id', 128)->nullable()->after('payment_status');
            $table->string('razorpay_payment_id', 128)->nullable()->after('razorpay_order_id');
            $table->string('razorpay_signature', 256)->nullable()->after('razorpay_payment_id');
            
            // Make activated_at and expires_at nullable
            $table->dateTime('activated_at')->nullable()->change();
            $table->dateTime('expires_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seeker_package_purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature']);
            
            // Revert changes to activated_at and expires_at
            $table->dateTime('activated_at')->nullable(false)->change();
            $table->dateTime('expires_at')->nullable(false)->change();
        });
    }
};
