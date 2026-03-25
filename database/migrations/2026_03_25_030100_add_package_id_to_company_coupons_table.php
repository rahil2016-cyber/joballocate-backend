<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_coupons', function (Blueprint $table) {
            $table->foreignId('company_subscription_package_id')
                ->nullable()
                ->after('is_active')
                ->constrained('company_subscription_packages')
                ->nullOnDelete();

            $table->index(['company_subscription_package_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::table('company_coupons', function (Blueprint $table) {
            $table->dropIndex(['company_subscription_package_id', 'code']);
            $table->dropForeign(['company_subscription_package_id']);
            $table->dropColumn('company_subscription_package_id');
        });
    }
};

