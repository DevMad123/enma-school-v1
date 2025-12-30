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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // ex: "Contrôle 1", "Examen Trimestriel"
            $table->enum('type', ['devoir', 'controle', 'examen', 'composition']);
            $table->decimal('coefficient', 4, 2)->default(1.00);
            $table->decimal('max_grade', 4, 2)->default(20.00);
            
            // Relations
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('class_id')
                  ->constrained('school_classes')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('teacher_assignment_id')
                  ->constrained('teacher_assignments')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('grade_period_id')
                  ->constrained('grade_periods')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
                  
            $table->date('evaluation_date');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('description')->nullable();
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['academic_year_id', 'subject_id', 'class_id']);
            $table->index(['grade_period_id', 'status']);
            $table->index('evaluation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
