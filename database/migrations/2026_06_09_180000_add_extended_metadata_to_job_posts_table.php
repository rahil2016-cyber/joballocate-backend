<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->string('role_category', 200)->nullable()->after('industry_type');
            $table->string('functional_area', 200)->nullable()->after('role_category');
            $table->text('education')->nullable()->after('functional_area');
        });
    }

    public function down(): void
    {
        Schema::table('job_posts', function (Blueprint $table) {
            $table->dropColumn(['role_category', 'functional_area', 'education']);
        });
    }
};
