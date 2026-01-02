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
        Schema::table('u_f_r_s', function (Blueprint $table) {
            $table->text('address')->nullable()->after('building'); // Adresse complète de l'UFR
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('u_f_r_s', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
