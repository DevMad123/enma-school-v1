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
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('grade_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_class_id')->constrained('classes')->onDelete('cascade');
            
            // Moyennes calculées
            $table->decimal('general_average', 5, 2)->nullable();
            $table->integer('class_rank')->nullable();
            $table->integer('total_students_in_class')->nullable();
            
            // Statistiques
            $table->integer('total_subjects')->default(0);
            $table->integer('subjects_passed')->default(0);
            $table->decimal('attendance_rate', 5, 2)->nullable();
            
            // Décision et mentions
            $table->enum('decision', ['admis', 'ajourné', 'exclu'])->nullable();
            $table->string('mention')->nullable(); // Très bien, Bien, Assez bien, Passable
            $table->text('observations')->nullable();
            
            // Métadonnées
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users');
            $table->boolean('is_final')->default(false);
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['student_id', 'academic_year_id', 'grade_period_id']);
            $table->unique(['student_id', 'academic_year_id', 'grade_period_id'], 'unique_student_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
