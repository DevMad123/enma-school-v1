<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_fee_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'payment_date',
        'status',
        'notes',
        'processed_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime'
    ];

    const PAYMENT_METHODS = [
        'cash' => 'Espèces',
        'bank_transfer' => 'Virement bancaire',
        'check' => 'Chèque',
        'card' => 'Carte bancaire',
        'mobile_money' => 'Mobile Money'
    ];

    const STATUSES = [
        'pending' => 'En attente',
        'confirmed' => 'Confirmé',
        'cancelled' => 'Annulé',
        'refunded' => 'Remboursé'
    ];

    /**
     * Relation vers l'étudiant
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Relation vers les frais scolaires
     */
    public function schoolFee(): BelongsTo
    {
        return $this->belongsTo(SchoolFee::class);
    }

    /**
     * Relation vers l'utilisateur qui a traité le paiement
     */
    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Relation vers le reçu
     */
    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    /**
     * Confirme le paiement et génère un reçu
     */
    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);
        
        // Générer automatiquement un reçu
        $this->generateReceipt();
    }

    /**
     * Génère un reçu pour ce paiement
     */
    public function generateReceipt(): Receipt
    {
        return Receipt::create([
            'payment_id' => $this->id,
            'receipt_number' => Receipt::generateReceiptNumber(),
            'issue_date' => now(),
            'student_name' => $this->student->user->name,
            'amount_paid' => $this->amount,
            'payment_method' => $this->payment_method,
            'school_fee_description' => $this->schoolFee->name
        ]);
    }

    /**
     * Vérifie si le paiement est confirmé
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Formate la méthode de paiement
     */
    public function getFormattedPaymentMethodAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Formate le statut
     */
    public function getFormattedStatusAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
