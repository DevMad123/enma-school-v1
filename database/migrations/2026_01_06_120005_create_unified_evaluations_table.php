<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table unifiée pour toutes les évaluations (préuniv et universitaire)
     * Remplace la table evaluations existante avec support polymorphique
     */
    public function up(): void
    {
        Schema::create('unified_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('educational_subject_id')->constrained('educational_subjects')->onDelete('cascade');
            
            // Référence polymorphique au contexte éducatif
            $table->string('evaluation_context_type')->nullable();
            $table->unsignedBigInteger('evaluation_context_id')->nullable();
            
            // Informations de l'évaluation
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('evaluation_type', [
                // Préuniversitaire
                'devoir_surveille',      // Devoir surveillé
                'interrogation_ecrite',  // Interrogation écrite
                'composition',           // Composition
                'exposé',               // Exposé oral
                'travail_pratique',     // Travail pratique
                'controle_continu',     // Contrôle continu
                
                // Universitaire LMD
                'partiel',              // Examen partiel
                'final',                // Examen final
                'cc',                   // Contrôle continu
                'rattrapage',           // Rattrapage
                'tp_evaluation',        // Évaluation TP
                'projet',               // Projet
                'stage_evaluation',     // Évaluation stage
                'soutenance',           // Soutenance
                'memoire'               // Mémoire
            ]);
            
            // Pondération et notation
            $table->decimal('coefficient', 3, 2)->default(1.00);
            $table->decimal('max_score', 5, 2)->default(20.00); // Note maximale
            $table->decimal('min_passing_score', 5, 2)->default(10.00); // Note de passage
            
            // Dates et timing
            $table->dateTime('evaluation_date');
            $table->time('duration')->nullable(); // Durée de l'évaluation
            $table->string('location')->nullable(); // Lieu de l'évaluation
            
            // Statut et validation
            $table->enum('status', [
                'planned',      // Planifiée
                'ongoing',      // En cours
                'completed',    // Terminée
                'graded',       // Notée
                'validated',    // Validée
                'cancelled'     // Annulée
            ])->default('planned');
            
            $table->boolean('is_published')->default(false); // Notes publiées
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('validated_at')->nullable();
            
            // Contexte éducatif
            $table->enum('educational_context', ['preuniversity', 'university']);
            $table->json('evaluation_criteria')->nullable(); // Critères d'évaluation
            $table->text('instructions')->nullable(); // Instructions spéciales
            
            // Statistiques générées
            $table->decimal('class_average', 4, 2)->nullable(); // Moyenne de classe
            $table->decimal('highest_score', 5, 2)->nullable(); // Plus haute note
            $table->decimal('lowest_score', 5, 2)->nullable(); // Plus basse note
            $table->integer('participants_count')->default(0); // Nombre de participants
            
            // Métadonnées extensibles
            $table->json('evaluation_metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['school_id', 'educational_context']);
            $table->index(['educational_subject_id', 'status']);
            $table->index(['evaluation_date', 'evaluation_type']);
            $table->index(['evaluation_context_type', 'evaluation_context_id'], 'unified_eval_context_idx');
            $table->index(['is_published']);
            $table->index(['created_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_evaluations');
    }
};