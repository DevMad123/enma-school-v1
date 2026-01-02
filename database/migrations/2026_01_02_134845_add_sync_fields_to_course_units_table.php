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
            // Champs pour la synchronisation automatique avec ECUE
            $table->boolean('sync_credits_with_elements')->default(true)
                  ->comment('Synchroniser automatiquement les crédits avec les ECUE');
            $table->boolean('sync_hours_with_elements')->default(true)
                  ->comment('Synchroniser automatiquement les heures avec les ECUE');
            $table->boolean('auto_sync_enabled')->default(true)
                  ->comment('Activer la synchronisation automatique générale');
            
            // Champs calculés et statistiques
            $table->integer('elements_count')->default(0)
                  ->comment('Nombre total d\'ECUE dans cette UE');
            $table->json('hours_distribution')->nullable()
                  ->comment('Répartition des heures en pourcentages (CM, TD, TP)');
            $table->timestamp('last_element_sync')->nullable()
                  ->comment('Dernière synchronisation avec les ECUE');
            
            // Index pour optimiser les requêtes
            $table->index(['auto_sync_enabled', 'elements_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_units', function (Blueprint $table) {
            $table->dropIndex(['auto_sync_enabled', 'elements_count']);
            $table->dropColumn([
                'sync_credits_with_elements',
                'sync_hours_with_elements', 
                'auto_sync_enabled',
                'elements_count',
                'hours_distribution',
                'last_element_sync'
            ]);
        });
    }
};
