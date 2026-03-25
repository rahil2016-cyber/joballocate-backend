<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_subscription_payments', function (Blueprint $table) {
            // Use explicit short names (MySQL 64-char identifier limit).
            $table->unsignedBigInteger('company_subscription_package_id')
                ->nullable()
                ->after('company_id');

            $table->foreign('company_subscription_package_id', 'cs_pay_pkg_fk')
                ->references('id')
                ->on('company_subscription_packages')
                ->nullOnDelete();

            $table->index(
                ['company_id', 'company_subscription_package_id', 'coupon_code_used'],
                'cs_pay_company_pkg_coupon_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('company_subscription_payments', function (Blueprint $table) {
            $table->dropIndex('cs_pay_company_pkg_coupon_idx');
            $table->dropForeign('cs_pay_pkg_fk');
            $table->dropColumn('company_subscription_package_id');
        });
    }
};

