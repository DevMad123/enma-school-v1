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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entity'); // course, assignment, student, payment, etc.
            $table->unsignedBigInteger('entity_id')->nullable(); // ID de l'entité concernée
            $table->string('action'); // created, updated, deleted, viewed, submitted, etc.
            $table->json('properties')->nullable(); // Données avant/après pour audit
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['user_id', 'created_at']);
            $table->index(['entity', 'entity_id']);
            $table->index(['action', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};