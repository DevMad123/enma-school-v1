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
        Schema::create('teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('subject_id')->nullable(); // Sera connecté aux subjects dans le module suivant
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['teacher_id', 'academic_year_id', 'class_id']);
            
            // Contrainte unique : un enseignant ne peut pas être affecté deux fois à la même classe pour la même matière et la même année
            $table->unique(['teacher_id', 'academic_year_id', 'class_id', 'subject_id'], 'teacher_class_subject_year_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_assignments');
    }
};
