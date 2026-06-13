<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('company_subscription_payments', 'company_subscription_package_id')) {
            Schema::table('company_subscription_payments', function (Blueprint $table) {
                // Use explicit short names (MySQL 64-char identifier limit).
                $table->unsignedBigInteger('company_subscription_package_id')
                    ->nullable()
                    ->after('company_id');
            });
        }

        $schema = DB::getDatabaseName();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isSqlite) {
            $indexes = Schema::getIndexes('company_subscription_payments');
            $indexExists = collect($indexes)->contains(fn($index) => $index['name'] === 'cs_pay_company_pkg_coupon_idx');

            $foreignKeys = Schema::getForeignKeys('company_subscription_payments');
            $fkExists = collect($foreignKeys)->contains(fn($fk) => $fk['name'] === 'cs_pay_pkg_fk');
        } else {
            $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', $schema)
                ->where('TABLE_NAME', 'company_subscription_payments')
                ->where('CONSTRAINT_NAME', 'cs_pay_pkg_fk')
                ->exists();

            $indexExists = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $schema)
                ->where('TABLE_NAME', 'company_subscription_payments')
                ->where('INDEX_NAME', 'cs_pay_company_pkg_coupon_idx')
                ->exists();
        }

        if (! $fkExists) {
            Schema::table('company_subscription_payments', function (Blueprint $table) {
                $table->foreign('company_subscription_package_id', 'cs_pay_pkg_fk')
                    ->references('id')
                    ->on('company_subscription_packages')
                    ->nullOnDelete();
            });
        }

        if (! $indexExists) {
            Schema::table('company_subscription_payments', function (Blueprint $table) {
                $table->index(
                    ['company_id', 'company_subscription_package_id', 'coupon_code_used'],
                    'cs_pay_company_pkg_coupon_idx'
                );
            });
        }
    }

    public function down(): void
    {
        $schema = DB::getDatabaseName();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        if ($isSqlite) {
            $indexes = Schema::getIndexes('company_subscription_payments');
            $indexExists = collect($indexes)->contains(fn($index) => $index['name'] === 'cs_pay_company_pkg_coupon_idx');

            $foreignKeys = Schema::getForeignKeys('company_subscription_payments');
            $fkExists = collect($foreignKeys)->contains(fn($fk) => $fk['name'] === 'cs_pay_pkg_fk');
        } else {
            $indexExists = DB::table('information_schema.STATISTICS')
                ->where('TABLE_SCHEMA', $schema)
                ->where('TABLE_NAME', 'company_subscription_payments')
                ->where('INDEX_NAME', 'cs_pay_company_pkg_coupon_idx')
                ->exists();

            $fkExists = DB::table('information_schema.TABLE_CONSTRAINTS')
                ->where('CONSTRAINT_SCHEMA', $schema)
                ->where('TABLE_NAME', 'company_subscription_payments')
                ->where('CONSTRAINT_NAME', 'cs_pay_pkg_fk')
                ->exists();
        }

        Schema::table('company_subscription_payments', function (Blueprint $table) use ($indexExists, $fkExists) {
            if ($indexExists) {
                $table->dropIndex('cs_pay_company_pkg_coupon_idx');
            }
            if ($fkExists) {
                $table->dropForeign('cs_pay_pkg_fk');
            }
            if (Schema::hasColumn('company_subscription_payments', 'company_subscription_package_id')) {
                $table->dropColumn('company_subscription_package_id');
            }
        });
    }
};

