<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table spécialisée pour les étudiants pré-universitaires (primaire, collège, lycée)
     */
    public function up(): void
    {
        Schema::create('preuniversity_students', function (Blueprint $table) {
            $table->id();
            
            // Données spécifiques pré-universitaires
            $table->string('student_matricule')->unique(); // Matricule élève (ex: ELV2024001)
            $table->enum('academic_serie', [
                'A1', 'A2', 'B', 'C', 'D', 'E', 'F1', 'F2', 'F3', 'F4', 'G1', 'G2', 'G3'
            ])->nullable(); // Série pour les lycéens
            
            // Données pédagogiques spéciales
            $table->boolean('is_repeater')->default(false); // Redoublant
            $table->integer('repeat_count')->default(0); // Nombre de redoublements
            $table->string('previous_school')->nullable(); // École d'origine
            $table->year('entry_year'); // Année d'entrée dans l'établissement
            
            // Informations tuteur/parent
            $table->foreignId('parent_profile_id')->nullable()->constrained('parent_profiles')->onDelete('set null');
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            
            // Données médicales/spéciales
            $table->text('medical_conditions')->nullable();
            $table->text('special_needs')->nullable();
            $table->boolean('has_transport')->default(false);
            $table->string('transport_route')->nullable();
            
            // Suivi disciplinaire
            $table->integer('conduct_points')->default(20); // Points de conduite (système disciplinaire)
            $table->text('behavioral_notes')->nullable();
            
            // Performances académiques spécifiques
            $table->decimal('yearly_average', 4, 2)->nullable(); // Moyenne annuelle
            $table->integer('class_rank')->nullable(); // Rang dans la classe
            $table->integer('total_absences')->default(0); // Total absences
            $table->integer('tardiness_count')->default(0); // Retards
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['student_matricule']);
            $table->index(['academic_serie']);
            $table->index(['is_repeater']);
            $table->index(['entry_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preuniversity_students');
    }
};