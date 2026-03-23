<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('location', 255)->nullable()->after('gst_number');
            $table->unsignedSmallInteger('established_year')->nullable()->after('location');
            $table->text('company_bio')->nullable()->after('established_year');
            $table->text('what_we_do')->nullable()->after('company_bio');
            $table->json('team_members')->nullable()->after('what_we_do');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'location',
                'established_year',
                'company_bio',
                'what_we_do',
                'team_members',
            ]);
        });
    }
};
