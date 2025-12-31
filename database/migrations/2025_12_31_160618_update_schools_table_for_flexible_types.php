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
        Schema::table('schools', function (Blueprint $table) {
            // Modifier l'enum existant pour les nouveaux types
            $table->dropColumn('type');
        });
        
        Schema::table('schools', function (Blueprint $table) {
            // Recréer la colonne type avec les nouveaux types
            $table->enum('type', ['pre_university', 'university'])->default('pre_university')->after('short_name');
            
            // Ajouter des colonnes pour la configuration flexible
            $table->json('educational_levels')->nullable()->after('type')
                ->comment('Niveaux éducatifs: ["primary", "secondary", "technical"]');
            $table->json('academic_structure_config')->nullable()->after('educational_levels')
                ->comment('Configuration de la structure académique');
        });
        
        // Migrer les données existantes
        \DB::table('schools')->where('type', 'secondary')->update([
            'type' => 'pre_university',
            'educational_levels' => json_encode(['primary', 'secondary'])
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['educational_levels', 'academic_structure_config']);
            $table->dropColumn('type');
        });
        
        Schema::table('schools', function (Blueprint $table) {
            $table->enum('type', ['secondary', 'university'])->default('secondary')->after('short_name');
        });
    }
};
