<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banner_ads', function (Blueprint $table) {
            $table->string('below_line', 500)->nullable()->after('content');
        });
    }

    public function down(): void
    {
        Schema::table('banner_ads', function (Blueprint $table) {
            $table->dropColumn('below_line');
        });
    }
};
