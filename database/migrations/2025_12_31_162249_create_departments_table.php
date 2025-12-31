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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('ufr_id')->constrained('u_f_r_s')->onDelete('cascade');
            $table->string('name'); // Ex: "Département d'Informatique"
            $table->string('code')->unique(); // Ex: "DEPT-INFO"
            $table->string('short_name')->nullable(); // Ex: "INFO"
            $table->text('description')->nullable();
            $table->string('head_of_department')->nullable(); // Chef de département
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('office_location')->nullable(); // Bureau
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
