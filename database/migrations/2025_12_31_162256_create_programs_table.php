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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "Licence Informatique"
            $table->string('code')->unique(); // Ex: "LIC-INFO"
            $table->string('short_name')->nullable(); // Ex: "L3 INFO"
            $table->enum('level', ['licence', 'master', 'doctorat', 'dut', 'bts']); // Niveau d'études
            $table->integer('duration_semesters'); // Durée en semestres (ex: 6 pour licence)
            $table->integer('total_credits'); // Total crédits ECTS (ex: 180 pour licence)
            $table->text('description')->nullable();
            $table->json('objectives')->nullable(); // Objectifs pédagogiques
            $table->string('diploma_title'); // Titre du diplôme
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
