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
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('academic_track_id')->nullable()->after('level_id')->constrained('academic_tracks')->onDelete('set null');
            $table->boolean('is_active')->default(true)->after('capacity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['academic_track_id']);
            $table->dropColumn(['academic_track_id', 'is_active']);
        });
    }
};
