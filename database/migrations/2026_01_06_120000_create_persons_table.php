<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Table de base pour toutes les personnes du système (étudiants, personnel, etc.)
     * Implémente l'architecture polymorphique unifiée
     */
    public function up(): void
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            
            // Données personnelles communes
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth');
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            
            // Informations de contact avancées
            $table->string('email')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            
            // Données administratives
            $table->string('matricule')->unique(); // Matricule unique universel
            $table->enum('status', ['active', 'suspended', 'left', 'graduated', 'transferred'])->default('active');
            $table->date('registration_date')->default(now());
            
            // Métadonnées système
            $table->json('metadata')->nullable(); // Données flexibles par type
            $table->timestamps();
            
            // Index pour performance
            $table->index(['school_id', 'status']);
            $table->index(['matricule', 'school_id']);
            $table->index(['first_name', 'last_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};