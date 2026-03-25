<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_subscription_payments', function (Blueprint $table) {
            $table->foreignId('company_subscription_package_id')
                ->nullable()
                ->after('company_id')
                ->constrained('company_subscription_packages')
                ->nullOnDelete();

            $table->index(['company_id', 'company_subscription_package_id', 'coupon_code_used']);
        });
    }

    public function down(): void
    {
        Schema::table('company_subscription_payments', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'company_subscription_package_id', 'coupon_code_used']);
            $table->dropForeign(['company_subscription_package_id']);
            $table->dropColumn('company_subscription_package_id');
        });
    }
};

