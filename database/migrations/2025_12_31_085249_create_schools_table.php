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
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->enum('type', ['secondary', 'university'])->default('secondary');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Côte d\'Ivoire');
            $table->string('logo_path')->nullable();
            $table->string('stamp_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->enum('academic_system', ['trimestre', 'semestre'])->default('trimestre');
            $table->enum('grading_system', ['20', '100', 'custom'])->default('20');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schools');
    }
};
