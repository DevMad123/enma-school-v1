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
        Schema::create('u_f_r_s', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Ex: "UFR Sciences et Technologies"
            $table->string('code')->unique(); // Ex: "UFR-ST"
            $table->string('short_name')->nullable(); // Ex: "ST"
            $table->text('description')->nullable();
            $table->string('dean_name')->nullable(); // Nom du doyen
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('building')->nullable(); // Bâtiment
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('u_f_r_s');
    }
};
