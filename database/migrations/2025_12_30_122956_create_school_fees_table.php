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
        Schema::create('school_fees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom des frais (ex: "Frais de scolarité", "Frais d'inscription")
            $table->text('description')->nullable(); // Description détaillée
            $table->decimal('amount', 10, 2); // Montant des frais
            
            // Relations optionnelles pour cibler spécifiquement
            $table->foreignId('school_class_id')->nullable()->constrained('school_classes')->onDelete('cascade');
            $table->foreignId('level_id')->nullable()->constrained('levels')->onDelete('cascade'); 
            $table->foreignId('cycle_id')->nullable()->constrained('cycles')->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            
            $table->boolean('is_mandatory')->default(true); // Frais obligatoires ou optionnels
            $table->date('due_date')->nullable(); // Date limite de paiement
            $table->enum('status', ['active', 'inactive'])->default('active');
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['academic_year_id', 'school_class_id']);
            $table->index(['level_id', 'cycle_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_fees');
    }
};
