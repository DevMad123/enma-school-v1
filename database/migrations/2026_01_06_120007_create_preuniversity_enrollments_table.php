<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table unifiée pour les inscriptions préuniversitaires (remplacement class_student)
     */
    public function up(): void
    {
        Schema::create('preuniversity_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unified_student_id')->constrained('unified_students')->onDelete('cascade');
            $table->foreignId('school_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('academic_period_id')->nullable()->constrained('academic_periods')->onDelete('set null');
            
            // Statut d'inscription
            $table->enum('enrollment_status', [
                'pending',      // En attente
                'enrolled',     // Inscrit
                'suspended',    // Suspendu
                'transferred',  // Transféré
                'withdrawn'     // Retiré
            ])->default('pending');
            
            // Dates importantes
            $table->date('enrollment_date');
            $table->date('effective_date')->nullable(); // Date d'effet
            $table->date('completion_date')->nullable(); // Date de fin
            
            // Informations pédagogiques
            $table->boolean('is_repeater')->default(false);
            $table->string('previous_class')->nullable(); // Classe précédente si redoublant
            $table->text('enrollment_notes')->nullable();
            
            // Informations financières
            $table->decimal('enrollment_fees', 10, 2)->default(0.00);
            $table->boolean('fees_paid')->default(false);
            $table->date('payment_deadline')->nullable();
            
            // Validation administrative
            $table->boolean('admin_validated')->default(false);
            $table->boolean('pedagogical_validated')->default(false);
            $table->foreignId('validated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->dateTime('validated_at')->nullable();
            
            // Métadonnées
            $table->json('enrollment_metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['unified_student_id', 'academic_year_id'], 'preuniv_enroll_student_year_idx');
            $table->index(['school_class_id', 'enrollment_status'], 'preuniv_enroll_class_status_idx');
            $table->index(['enrollment_date'], 'preuniv_enroll_date_idx');
            $table->index(['enrollment_status'], 'preuniv_enroll_status_idx');
            
            // Contrainte d'unicité : un étudiant par classe par année
            $table->unique(['unified_student_id', 'school_class_id', 'academic_year_id'], 'unique_student_class_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('preuniversity_enrollments');
    }
};