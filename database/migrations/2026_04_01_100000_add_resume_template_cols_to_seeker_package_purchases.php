<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seeker_package_purchases', function (Blueprint $table) {
            if (! Schema::hasColumn('seeker_package_purchases', 'resume_template_id')) {
                $table->unsignedInteger('resume_template_id')->nullable()->after('resume_builds_granted');
            }
            if (! Schema::hasColumn('seeker_package_purchases', 'resume_template_title')) {
                $table->string('resume_template_title', 200)->nullable()->after('resume_template_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seeker_package_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('seeker_package_purchases', 'resume_template_title')) {
                $table->dropColumn('resume_template_title');
            }
            if (Schema::hasColumn('seeker_package_purchases', 'resume_template_id')) {
                $table->dropColumn('resume_template_id');
            }
        });
    }
};
