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
        Schema::create('course_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('semester_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "Algorithmique et Programmation"
            $table->string('code')->unique(); // Ex: "INFO-101"
            $table->string('short_name')->nullable(); // Ex: "ALGO"
            $table->enum('type', ['obligatoire', 'optionnel', 'libre']); // Type d'UE
            $table->integer('credits'); // Crédits ECTS (ex: 6)
            $table->integer('hours_cm')->default(0); // Heures cours magistral
            $table->integer('hours_td')->default(0); // Heures travaux dirigés
            $table->integer('hours_tp')->default(0); // Heures travaux pratiques
            $table->integer('hours_personal')->default(0); // Heures travail personnel
            $table->text('description')->nullable();
            $table->json('prerequisites')->nullable(); // Prérequis
            $table->json('learning_outcomes')->nullable(); // Résultats d'apprentissage
            $table->enum('evaluation_method', ['controle_continu', 'examen_final', 'mixte'])->default('mixte');
            $table->decimal('coefficient', 5, 2)->default(1.00); // Coefficient
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_units');
    }
};
