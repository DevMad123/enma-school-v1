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
        Schema::create('teacher_program_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade');
            $table->foreignId('semester_id')->nullable()->constrained('semesters')->onDelete('set null');
            $table->foreignId('course_unit_id')->nullable()->constrained('course_units')->onDelete('set null');
            $table->integer('weekly_hours')->default(0); // Nombre d'heures par semaine
            $table->string('assignment_type')->default('regular'); // regular, temporary, substitute
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['teacher_id', 'program_id', 'semester_id'], 'tpa_teacher_program_semester_idx');
            $table->index(['is_active', 'assignment_type'], 'tpa_status_type_idx');
            
            // Contrainte unique pour éviter les doublons d'affectation
            $table->unique(['teacher_id', 'program_id', 'semester_id', 'course_unit_id'], 'tpa_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_program_assignments');
    }
};
