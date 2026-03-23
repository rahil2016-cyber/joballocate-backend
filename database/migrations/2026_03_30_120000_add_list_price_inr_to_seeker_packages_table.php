<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_packages', function (Blueprint $table) {
            $table->unsignedInteger('list_price_inr')->nullable()->after('price_inr');
        });
    }

    public function down(): void
    {
        Schema::table('seeker_packages', function (Blueprint $table) {
            $table->dropColumn('list_price_inr');
        });
    }
};
