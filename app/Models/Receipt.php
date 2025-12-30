<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'receipt_number',
        'issue_date',
        'student_name',
        'amount_paid',
        'payment_method',
        'school_fee_description',
        'notes'
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'issue_date' => 'datetime'
    ];

    /**
     * Relation vers le paiement
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Génère un numéro de reçu unique
     */
    public static function generateReceiptNumber(): string
    {
        $year = date('Y');
        $lastReceipt = static::whereYear('issue_date', $year)
                            ->orderBy('id', 'desc')
                            ->first();
        
        if ($lastReceipt) {
            $lastNumber = (int) substr($lastReceipt->receipt_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'REC-' . $year . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Génère le contenu PDF du reçu
     */
    public function generatePdfContent(): string
    {
        return view('finance.receipts.pdf', [
            'receipt' => $this,
            'payment' => $this->payment,
            'student' => $this->payment->student,
            'schoolFee' => $this->payment->schoolFee
        ])->render();
    }

    /**
     * Formate le montant pour l'affichage
     */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_paid, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Formate la date d'émission
     */
    public function getFormattedIssueDateAttribute(): string
    {
        return $this->issue_date->format('d/m/Y à H:i');
    }
}
