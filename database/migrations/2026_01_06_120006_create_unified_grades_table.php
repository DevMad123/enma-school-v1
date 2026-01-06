<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table unifiée pour toutes les notes (préuniv et universitaire)
     */
    public function up(): void
    {
        Schema::create('unified_grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('unified_student_id')->constrained('unified_students')->onDelete('cascade');
            $table->foreignId('unified_evaluation_id')->constrained('unified_evaluations')->onDelete('cascade');
            
            // Note et statut
            $table->decimal('score', 5, 2)->nullable(); // Note obtenue
            $table->decimal('max_score', 5, 2)->default(20.00); // Note maximale
            $table->enum('grade_status', [
                'absent',           // Absent
                'excused',          // Absent excusé
                'present',          // Présent
                'cheating',         // Fraude
                'incomplete',       // Devoir incomplet
                'invalid'           // Note invalidée
            ])->default('present');
            
            // Contexte de notation
            $table->enum('educational_context', ['preuniversity', 'university']);
            $table->text('comments')->nullable(); // Commentaires du correcteur
            $table->json('detailed_scores')->nullable(); // Détail par question/compétence
            
            // Validation et suivi
            $table->boolean('is_validated')->default(false);
            $table->foreignId('graded_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('graded_at');
            $table->dateTime('validated_at')->nullable();
            
            // Données universitaires spécifiques (LMD)
            $table->decimal('ects_points', 5, 2)->nullable(); // Points ECTS obtenus
            $table->enum('lmd_grade', [
                'A+', 'A', 'B+', 'B', 'C+', 'C', 'D+', 'D', 'F'
            ])->nullable(); // Grade LMD
            $table->boolean('ue_validated')->nullable(); // UE validée (universitaire)
            $table->boolean('can_compensate')->nullable(); // Compensation possible
            
            // Données préuniversitaires spécifiques
            $table->decimal('weighted_score', 5, 2)->nullable(); // Note pondérée par coefficient
            $table->enum('appreciation', [
                'excellent', 'tres_bien', 'bien', 'assez_bien', 
                'passable', 'insuffisant', 'mediocre'
            ])->nullable(); // Appréciation
            
            // Suivi des améliorations
            $table->integer('improvement_percentage')->nullable(); // % d'amélioration
            $table->text('teacher_feedback')->nullable(); // Feedback enseignant
            $table->text('remedial_actions')->nullable(); // Actions correctives
            
            // Métadonnées extensibles
            $table->json('grade_metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['school_id', 'educational_context']);
            $table->index(['unified_student_id', 'unified_evaluation_id']);
            $table->unique(['unified_student_id', 'unified_evaluation_id'], 'unique_student_evaluation');
            $table->index(['score', 'grade_status']);
            $table->index(['is_validated']);
            $table->index(['graded_by']);
            $table->index(['graded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_grades');
    }
};