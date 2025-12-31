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
        Schema::table('subjects', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->after('id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('level_id')->nullable()->after('coefficient')->constrained('levels')->onDelete('cascade');
            $table->foreignId('academic_track_id')->nullable()->after('level_id')->constrained('academic_tracks')->onDelete('cascade');
            $table->integer('credit')->nullable()->after('coefficient'); // Crédits ECTS pour université
            $table->integer('volume_hour')->nullable()->after('credit'); // Volume horaire
            $table->boolean('is_active')->default(true)->after('volume_hour');
        });

        // Ajouter les index
        Schema::table('subjects', function (Blueprint $table) {
            $table->index(['school_id', 'level_id']);
            $table->index(['academic_track_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['academic_track_id']);
            $table->dropIndex(['school_id', 'level_id']);
            $table->dropIndex(['academic_track_id']);
            $table->dropColumn(['school_id', 'level_id', 'academic_track_id', 'credit', 'volume_hour', 'is_active']);
        });
    }
};
