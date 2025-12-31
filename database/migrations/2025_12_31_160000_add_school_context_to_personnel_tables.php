<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * MODULE A4 - Ajout du contexte école aux tables personnel
     */
    public function up(): void
    {
        // Ajouter school_id à la table teachers
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('employee_id')->nullable()->unique(); // Numéro d'employé
            $table->date('hire_date')->nullable(); // Date d'embauche
            $table->text('qualifications')->nullable(); // Qualifications/diplômes
            $table->json('teaching_subjects')->nullable(); // Matières enseignées (JSON pour flexibilité)
            
            // Index pour optimiser les requêtes par école
            $table->index('school_id');
        });

        // Ajouter school_id à la table staff
        Schema::table('staff', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->string('employee_id')->nullable()->unique(); // Numéro d'employé
            $table->date('hire_date')->nullable(); // Date d'embauche
            $table->string('department')->nullable(); // Département/service
            $table->text('responsibilities')->nullable(); // Responsabilités
            
            // Index pour optimiser les requêtes par école
            $table->index('school_id');
        });

        // Améliorer la table teacher_assignments avec plus de flexibilité
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->string('assignment_type')->default('regular'); // regular, substitute, temporary
            $table->date('start_date')->nullable(); // Date de début d'affectation
            $table->date('end_date')->nullable(); // Date de fin (pour affectations temporaires)
            $table->integer('weekly_hours')->nullable(); // Nombre d'heures par semaine
            $table->text('notes')->nullable(); // Notes sur l'affectation
            $table->boolean('is_active')->default(true); // Statut actif/inactif
            
            // Index pour les requêtes par statut et dates
            $table->index(['is_active', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_assignments', function (Blueprint $table) {
            $table->dropColumn([
                'assignment_type', 'start_date', 'end_date', 
                'weekly_hours', 'notes', 'is_active'
            ]);
            $table->dropIndex(['is_active', 'start_date', 'end_date']);
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn([
                'school_id', 'employee_id', 'hire_date', 
                'department', 'responsibilities'
            ]);
        });

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropIndex(['school_id']);
            $table->dropColumn([
                'school_id', 'employee_id', 'hire_date', 
                'qualifications', 'teaching_subjects'
            ]);
        });
    }
};