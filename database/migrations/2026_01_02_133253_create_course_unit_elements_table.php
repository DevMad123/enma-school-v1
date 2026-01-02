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
        Schema::create('course_unit_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_unit_id')->constrained('course_units')->onDelete('cascade');
            $table->string('code', 20)->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('credits')->unsigned();
            $table->decimal('coefficient', 4, 2)->default(1.00);
            $table->integer('hours_cm')->default(0);
            $table->integer('hours_td')->default(0);
            $table->integer('hours_tp')->default(0);
            $table->integer('hours_total')->default(0);
            $table->enum('evaluation_type', ['controle_continu', 'examen_final', 'mixte'])->default('mixte');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Index composé pour unicité du code dans une UE
            $table->unique(['course_unit_id', 'code'], 'unique_ecue_code_per_ue');
            
            // Index pour les requêtes fréquentes
            $table->index(['course_unit_id', 'status'], 'idx_course_unit_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_unit_elements');
    }
};
