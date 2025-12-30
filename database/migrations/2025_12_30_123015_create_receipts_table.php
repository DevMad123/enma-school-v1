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
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            
            $table->string('receipt_number')->unique(); // Numéro de reçu unique
            $table->datetime('issue_date')->default(now()); // Date d'émission
            
            // Informations dénormalisées pour faciliter la génération PDF
            $table->string('student_name');
            $table->decimal('amount_paid', 10, 2);
            $table->string('payment_method');
            $table->string('school_fee_description');
            
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index('receipt_number');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
