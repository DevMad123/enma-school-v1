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
        Schema::table('academic_years', function (Blueprint $table) {
            $table->boolean('is_current')->default(false)->after('is_active');
            $table->boolean('is_archived')->default(false)->after('is_current');
            
            $table->index('is_current');
            $table->index('is_archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('academic_years', function (Blueprint $table) {
            $table->dropIndex(['is_current']);
            $table->dropIndex(['is_archived']);
            $table->dropColumn(['is_current', 'is_archived']);
        });
    }
};
