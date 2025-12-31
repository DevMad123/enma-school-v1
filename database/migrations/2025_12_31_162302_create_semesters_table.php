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
        Schema::create('semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('academic_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('program_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "Semestre 1", "S1"
            $table->integer('semester_number'); // 1, 2, 3, 4, 5, 6...
            $table->integer('academic_level'); // 1 (L1), 2 (L2), 3 (L3), 4 (M1), 5 (M2)...
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('required_credits'); // Crédits requis pour valider (ex: 30)
            $table->text('description')->nullable();
            $table->boolean('is_current')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['program_id', 'semester_number', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('semesters');
    }
};
