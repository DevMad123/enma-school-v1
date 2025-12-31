<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grade_periods', function (Blueprint $table) {
            // Vérifier si les colonnes n'existent pas déjà
            if (!Schema::hasColumn('grade_periods', 'type')) {
                $table->enum('type', ['trimestre', 'semestre'])->default('trimestre')->after('name');
            }
            if (!Schema::hasColumn('grade_periods', 'order')) {
                $table->tinyInteger('order')->unsigned()->default(1)->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_periods', function (Blueprint $table) {
            if (Schema::hasColumn('grade_periods', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('grade_periods', 'order')) {
                $table->dropColumn('order');
            }
        });
    }
};
