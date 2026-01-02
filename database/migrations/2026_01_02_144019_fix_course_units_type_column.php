<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('course_units', function (Blueprint $table) {
            // Modifier la colonne type pour utiliser un VARCHAR au lieu d'un ENUM
            $table->string('type', 50)->change();
            
            // Vérifier si les colonnes existent avant de les supprimer
            if (Schema::hasColumn('course_units', 'hours_personal')) {
                $table->dropColumn('hours_personal');
            }
            if (Schema::hasColumn('course_units', 'learning_outcomes')) {
                $table->dropColumn('learning_outcomes');
            }
            if (Schema::hasColumn('course_units', 'evaluation_method')) {
                $table->dropColumn('evaluation_method');
            }
        });
        
        // Mettre à jour les valeurs existantes
        DB::table('course_units')->update([
            'type' => DB::raw("CASE 
                WHEN type = 'obligatoire' THEN 'mandatory'
                WHEN type = 'optionnel' THEN 'optional' 
                WHEN type = 'libre' THEN 'optional'
                ELSE 'mandatory'
            END")
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remettre les valeurs originales avant de changer la structure
        DB::table('course_units')->update([
            'type' => DB::raw("CASE 
                WHEN type = 'mandatory' THEN 'obligatoire'
                WHEN type = 'optional' THEN 'optionnel'
                ELSE 'obligatoire'
            END")
        ]);
        
        Schema::table('course_units', function (Blueprint $table) {
            // Remettre l'enum original
            $table->enum('type', ['obligatoire', 'optionnel', 'libre'])->change();
            
            // Remettre les colonnes supprimées si elles n'existent pas
            if (!Schema::hasColumn('course_units', 'hours_personal')) {
                $table->integer('hours_personal')->default(0)->after('hours_tp');
            }
            if (!Schema::hasColumn('course_units', 'learning_outcomes')) {
                $table->json('learning_outcomes')->nullable()->after('prerequisites');
            }
            if (!Schema::hasColumn('course_units', 'evaluation_method')) {
                $table->enum('evaluation_method', ['controle_continu', 'examen_final', 'mixte'])->default('mixte')->after('learning_outcomes');
            }
        });
    }
};
