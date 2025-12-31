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
        Schema::table('school_settings', function (Blueprint $table) {
            // Ajouter des index pour améliorer les performances des requêtes
            // de paramètres pédagogiques spécifiques (level_X_validation_threshold, etc.)
            $table->index(['key'], 'school_settings_key_index');
            $table->index(['school_id', 'key'], 'school_settings_school_key_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_settings', function (Blueprint $table) {
            $table->dropIndex('school_settings_key_index');
            $table->dropIndex('school_settings_school_key_index');
        });
    }
};
