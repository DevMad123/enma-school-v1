<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table unifiée pour toutes les matières/UE du système éducatif
     * Remplace les tables subjects séparées et unifie PreUniv/Univ
     */
    public function up(): void
    {
        Schema::create('educational_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            
            // Référence polymorphique au niveau éducatif (Level ou Semester)
            $table->nullableMorphs('educational_level'); // educational_level_type, educational_level_id
            
            // Données communes matière/UE
            $table->string('name'); // Nom de la matière/UE
            $table->string('code')->unique(); // Code unique (ex: MATH-L1-S1, FRA-6EME)
            $table->text('description')->nullable();
            
            // Pondération et validation
            $table->decimal('coefficient', 3, 2)->default(1.00); // Coefficient pour calculs
            $table->integer('ects_credits')->nullable(); // Crédits ECTS (universitaire uniquement)
            $table->integer('volume_hours')->nullable(); // Volume horaire
            $table->integer('td_hours')->nullable(); // Heures TD
            $table->integer('tp_hours')->nullable(); // Heures TP
            
            // Type et catégorie
            $table->enum('subject_type', [
                'core_subject',      // Matière fondamentale (préuniv)
                'optional_subject',  // Matière optionnelle
                'fundamental_ue',    // UE fondamentale (univ)
                'complementary_ue',  // UE complémentaire
                'transversal_ue',    // UE transversale
                'optional_ue',       // UE optionnelle
                'internship',        // Stage
                'thesis'            // Mémoire/Thèse
            ]);
            
            $table->enum('evaluation_mode', [
                'continuous',        // Évaluation continue
                'final_exam',       // Examen final
                'mixed',            // Mixte
                'practical'         // Évaluation pratique
            ])->default('continuous');
            
            // Contexte éducatif
            $table->enum('educational_context', ['preuniversity', 'university']);
            $table->string('academic_domain')->nullable(); // Domaine académique
            $table->json('prerequisites')->nullable(); // Prérequis (IDs d'autres matières/UE)
            
            // Gestion des enseignants
            $table->json('assigned_teachers')->nullable(); // IDs des enseignants assignés
            $table->foreignId('coordinator_id')->nullable()->constrained('teachers')->onDelete('set null');
            
            // Statut et organisation
            $table->boolean('is_active')->default(true);
            $table->boolean('is_mandatory')->default(true);
            $table->integer('display_order')->default(0);
            $table->enum('semester_period', ['S1', 'S2', 'yearly'])->default('yearly');
            
            // Métadonnées extensibles
            $table->json('subject_metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['school_id', 'educational_context']);
            $table->index(['educational_level_type', 'educational_level_id']);
            $table->index(['subject_type', 'is_active']);
            $table->index(['code']);
            $table->index(['coordinator_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educational_subjects');
    }
};