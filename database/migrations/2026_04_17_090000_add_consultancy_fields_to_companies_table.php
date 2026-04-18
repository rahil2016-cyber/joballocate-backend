<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('company_kind', 20)->default('company')->after('name');
            $table->string('consultancy_hiring_for', 160)->nullable()->after('website');
            $table->boolean('hide_hiring_company')->default(false)->after('consultancy_hiring_for');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'company_kind',
                'consultancy_hiring_for',
                'hide_hiring_company',
            ]);
        });
    }
};
