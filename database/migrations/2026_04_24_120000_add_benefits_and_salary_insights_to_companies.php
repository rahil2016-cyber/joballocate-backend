<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            if (! Schema::hasColumn('companies', 'benefits')) {
                $table->text('benefits')->nullable()->after('what_we_do');
            }
            if (! Schema::hasColumn('companies', 'salary_insights')) {
                $table->text('salary_insights')->nullable()->after('benefits');
            }
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $drop = [];
            foreach (['benefits', 'salary_insights'] as $column) {
                if (Schema::hasColumn('companies', $column)) {
                    $drop[] = $column;
                }
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
