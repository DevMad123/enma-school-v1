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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_fee_id')->constrained('school_fees')->onDelete('cascade');
            
            $table->decimal('amount', 10, 2); // Montant payé (peut être partiel)
            $table->enum('payment_method', ['cash', 'bank_transfer', 'check', 'card', 'mobile_money']);
            $table->string('transaction_reference')->nullable(); // Référence bancaire ou de transaction
            
            $table->datetime('payment_date')->default(now()); // Date du paiement
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('pending');
            
            $table->text('notes')->nullable(); // Notes additionnelles
            $table->foreignId('processed_by')->nullable()->constrained('users'); // Qui a traité le paiement
            
            $table->timestamps();
            
            // Index pour optimiser les requêtes
            $table->index(['student_id', 'school_fee_id']);
            $table->index(['payment_date', 'status']);
            $table->index('transaction_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
