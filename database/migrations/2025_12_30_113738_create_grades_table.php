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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            
            // Relations principales
            $table->foreignId('evaluation_id')
                  ->constrained('evaluations')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
                  
            // Note et statut
            $table->decimal('grade', 4, 2)->nullable(); // Note obtenue
            $table->boolean('absent')->default(false);
            $table->text('justification')->nullable(); // Justification absence ou note
            
            // Métadonnées de notation
            $table->foreignId('graded_by')
                  ->nullable()
                  ->constrained('users')
                  ->onUpdate('cascade')
                  ->onDelete('set null');
            $table->timestamp('graded_at')->nullable();
            $table->timestamps();

            // Contraintes et index
            $table->unique(['evaluation_id', 'student_id']); // Un élève = une note par évaluation
            $table->index(['student_id', 'evaluation_id']);
            $table->index(['evaluation_id', 'absent']);
            $table->index('graded_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};
