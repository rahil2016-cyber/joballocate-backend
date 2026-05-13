<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('industry_types', function (Blueprint $table) {
            $table->boolean('show_on_seeker_home')->default(false)->after('is_active');
            $table->unsignedInteger('seeker_home_sort_order')->default(0)->after('show_on_seeker_home');
            $table->string('seeker_home_icon', 64)->nullable()->after('seeker_home_sort_order');
            $table->string('seeker_home_search', 200)->nullable()->after('seeker_home_icon');
            $table->boolean('seeker_home_accent_dot')->default(false)->after('seeker_home_search');
        });
    }

    public function down(): void
    {
        Schema::table('industry_types', function (Blueprint $table) {
            $table->dropColumn([
                'show_on_seeker_home',
                'seeker_home_sort_order',
                'seeker_home_icon',
                'seeker_home_search',
                'seeker_home_accent_dot',
            ]);
        });
    }
};
