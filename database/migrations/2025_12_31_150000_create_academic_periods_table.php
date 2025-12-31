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
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->onDelete('cascade');
            $table->string('name'); // Trimestre 1, Semestre 1, etc.
            $table->string('short_name'); // T1, S1, etc.
            $table->integer('order')->default(1); // Ordre dans l'année
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_current')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['academic_year_id', 'is_current']);
            $table->index(['academic_year_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};