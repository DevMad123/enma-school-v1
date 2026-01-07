<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table Student polymorphique - Base commune pour tous les étudiants
     * Utilise studentable_type et studentable_id pour le polymorphisme
     */
    public function up(): void
    {
        Schema::create('unified_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('person_id')->constrained('persons')->onDelete('cascade');
            
            // Polymorphisme - Référence vers PreUniversityStudent ou UniversityStudent
            $table->nullableMorphs('studentable'); // studentable_type, studentable_id
            
            // Données académiques communes
            $table->string('student_number')->unique(); // Numéro d'étudiant distinct du matricule
            $table->date('academic_entry_date'); // Date d'entrée dans l'institution
            $table->enum('student_status', [
                'enrolled', 'suspended', 'graduated', 'transferred', 'dropped_out'
            ])->default('enrolled');
            
            // Données financières communes
            $table->boolean('financial_clearance')->default(false);
            $table->date('last_payment_date')->nullable();
            
            // Suivi académique global
            $table->json('academic_history')->nullable(); // Historique des années/classes
            $table->decimal('overall_gpa', 4, 2)->nullable(); // GPA global
            $table->integer('total_credits_earned')->default(0); // Crédits accumulés
            
            // Métadonnées extensibles
            $table->json('student_metadata')->nullable();
            
            $table->timestamps();
            
            // Index pour performance
            $table->index(['student_number', 'student_status']);
            $table->index(['person_id', 'student_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unified_students');
    }
};