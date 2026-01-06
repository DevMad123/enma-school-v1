<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table spécialisée pour les étudiants universitaires (LMD)
     */
    public function up(): void
    {
        Schema::create('university_students', function (Blueprint $table) {
            $table->id();
            
            // Données spécifiques universitaires LMD
            $table->string('university_matricule')->unique(); // Matricule universitaire (ex: UNI2024001)
            $table->enum('admission_type', [
                'direct', 'transfer', 'equivalence', 'competition'
            ])->default('direct');
            
            // Référence aux structures universitaires
            $table->foreignId('ufr_id')->constrained('u_f_r_s')->onDelete('cascade');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            
            // Progression LMD
            $table->enum('current_level', ['L1', 'L2', 'L3', 'M1', 'M2', 'D1', 'D2', 'D3'])->default('L1');
            $table->integer('current_semester')->default(1); // Semestre actuel
            $table->decimal('cumulative_gpa', 4, 2)->default(0.00); // GPA cumulé
            $table->integer('total_ects_credits')->default(0); // Total crédits ECTS
            $table->integer('required_credits')->default(180); // Crédits requis pour le diplôme (180 L, 120 M, etc.)
            
            // Historique académique LMD
            $table->json('semester_history')->nullable(); // Historique par semestre
            $table->json('ue_validations')->nullable(); // UE validées avec notes et crédits
            $table->json('compensation_history')->nullable(); // Historique des compensations
            
            // Informations admission et orientation
            $table->string('bac_series')->nullable(); // Série du BAC
            $table->decimal('bac_average', 4, 2)->nullable(); // Moyenne BAC
            $table->year('bac_year')->nullable(); // Année d'obtention BAC
            $table->string('previous_institution')->nullable(); // Établissement précédent
            
            // Données financières universitaires
            $table->decimal('tuition_fees', 10, 2)->default(0.00); // Frais de scolarité
            $table->boolean('scholarship_recipient')->default(false); // Boursier
            $table->string('scholarship_type')->nullable(); // Type de bourse
            $table->decimal('scholarship_amount', 10, 2)->nullable(); // Montant bourse
            
            // Suivi pédagogique LMD
            $table->text('academic_advisor')->nullable(); // Conseiller pédagogique
            $table->boolean('on_probation')->default(false); // En probation académique
            $table->integer('probation_semesters')->default(0); // Nombre de semestres en probation
            $table->text('remedial_actions')->nullable(); // Actions de rattrapage
            
            // Recherche et thèse (pour Master/Doctorat)
            $table->string('research_area')->nullable(); // Domaine de recherche
            $table->string('thesis_title')->nullable(); // Titre de thèse/mémoire
            $table->string('supervisor')->nullable(); // Directeur de thèse
            $table->date('thesis_defense_date')->nullable(); // Date de soutenance
            $table->enum('thesis_status', [
                'not_started', 'in_progress', 'submitted', 'defended', 'validated'
            ])->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['university_matricule']);
            $table->index(['ufr_id', 'department_id', 'program_id']);
            $table->index(['current_level', 'current_semester']);
            $table->index(['cumulative_gpa']);
            $table->index(['scholarship_recipient']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_students');
    }
};