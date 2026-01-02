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
        Schema::table('course_units', function (Blueprint $table) {
            $table->integer('hours_total')->default(0)->comment('Total hours calculated from ECUE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_units', function (Blueprint $table) {
            $table->dropColumn(['hours_total']);
        });
    }
};
