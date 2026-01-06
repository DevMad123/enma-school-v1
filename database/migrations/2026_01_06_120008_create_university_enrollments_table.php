<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table unifiée pour les inscriptions universitaires (par programme/semestre)
     */
    public function up(): void
    {
        Schema::create('university_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unified_student_id')->constrained('unified_students')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('semester_id')->constrained('semesters')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            
            // Statut d'inscription
            $table->enum('enrollment_status', [
                'pre_registered', // Pré-inscription
                'admitted',       // Admis
                'enrolled',       // Inscrit définitif
                'deferred',       // Différé
                'suspended',      // Suspendu
                'transferred',    // Transféré
                'graduated',      // Diplômé
                'dropped_out'     // Abandon
            ])->default('pre_registered');
            
            // Type d'inscription
            $table->enum('enrollment_type', [
                'first_time',     // Première inscription
                'returning',      // Réinscription
                'transfer',       // Transfert
                'readmission'     // Réadmission
            ])->default('first_time');
            
            // Dates importantes
            $table->date('application_date');
            $table->date('admission_date')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->date('semester_start_date')->nullable();
            $table->date('semester_end_date')->nullable();
            
            // Charge académique
            $table->integer('credits_enrolled')->default(0); // Crédits inscrits ce semestre
            $table->integer('min_credits')->default(24); // Minimum requis
            $table->integer('max_credits')->default(36); // Maximum autorisé
            
            // Informations financières
            $table->decimal('tuition_fees', 12, 2)->default(0.00);
            $table->decimal('administrative_fees', 10, 2)->default(0.00);
            $table->decimal('total_fees', 12, 2)->default(0.00);
            $table->boolean('fees_paid')->default(false);
            $table->date('payment_deadline')->nullable();
            $table->string('payment_method')->nullable();
            
            // Validation multi-niveaux
            $table->boolean('academic_validation')->default(false); // Validation académique
            $table->boolean('administrative_validation')->default(false); // Validation administrative
            $table->boolean('financial_validation')->default(false); // Validation financière
            $table->boolean('final_validation')->default(false); // Validation finale
            
            // Workflow de validation
            $table->foreignId('academic_validator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('admin_validator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('financial_validator_id')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('academic_validated_at')->nullable();
            $table->dateTime('admin_validated_at')->nullable();
            $table->dateTime('financial_validated_at')->nullable();
            $table->dateTime('final_validated_at')->nullable();
            
            // Prérequis académiques
            $table->json('prerequisites_checked')->nullable(); // Liste des prérequis vérifiés
            $table->boolean('prerequisites_met')->default(false);
            $table->text('prerequisites_notes')->nullable();
            
            // Performance tracking
            $table->decimal('expected_gpa', 4, 2)->nullable(); // GPA attendu
            $table->decimal('semester_gpa', 4, 2)->nullable(); // GPA du semestre
            $table->boolean('honor_roll')->default(false); // Tableau d'honneur
            $table->boolean('academic_warning')->default(false); // Avertissement académique
            
            // Documents requis
            $table->json('required_documents')->nullable(); // Liste documents requis
            $table->json('submitted_documents')->nullable(); // Documents soumis
            $table->boolean('documents_complete')->default(false);
            
            // Métadonnées étendues
            $table->json('enrollment_metadata')->nullable();
            $table->text('special_conditions')->nullable(); // Conditions spéciales
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['unified_student_id', 'academic_year_id']);
            $table->index(['program_id', 'semester_id']);
            $table->index(['enrollment_status', 'enrollment_type']);
            $table->index(['final_validation']);
            $table->index(['fees_paid']);
            $table->index(['semester_gpa']);
            
            // Contrainte d'unicité : un étudiant par programme par semestre par année
            $table->unique(['unified_student_id', 'program_id', 'semester_id', 'academic_year_id'], 'unique_student_program_semester_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('university_enrollments');
    }
};