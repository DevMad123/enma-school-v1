<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicPeriod extends Model
{
    protected $fillable = [
        'academic_year_id',
        'name',
        'short_name',
        'order',
        'start_date',
        'end_date',
        'is_active',
        'is_current',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_current' => 'boolean',
    ];

    /**
     * Relation avec l'année académique
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Scope pour obtenir la période courante
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope pour obtenir les périodes actives
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour obtenir les périodes d'une année académique
     */
    public function scopeForYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    /**
     * Scope pour ordonner par ordre croissant
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Méthode pour vérifier si la période est en cours
     */
    public function isCurrentPeriod(): bool
    {
        $now = now();
        return $this->start_date <= $now && $this->end_date >= $now && $this->is_active;
    }

    /**
     * Méthode pour obtenir le statut de la période
     */
    public function getStatus(): string
    {
        if (!$this->is_active) {
            return 'inactive';
        }

        $now = now();
        if ($this->start_date > $now) {
            return 'upcoming';
        } elseif ($this->end_date < $now) {
            return 'past';
        } else {
            return 'current';
        }
    }

    /**
     * Méthode pour générer les périodes standard selon le système académique
     */
    public static function generateStandardPeriods($academicYear, $system = 'trimestre'): array
    {
        $periods = [];
        $startDate = $academicYear->start_date;
        $endDate = $academicYear->end_date;
        
        $totalDays = $startDate->diffInDays($endDate);

        if ($system === 'trimestre') {
            // Division en 3 trimestres
            $periodDays = intval($totalDays / 3);
            
            for ($i = 1; $i <= 3; $i++) {
                $periodStart = $i === 1 ? $startDate : $startDate->copy()->addDays(($i - 1) * $periodDays);
                $periodEnd = $i === 3 ? $endDate : $startDate->copy()->addDays($i * $periodDays - 1);
                
                $periods[] = [
                    'name' => "Trimestre $i",
                    'short_name' => "T$i",
                    'order' => $i,
                    'start_date' => $periodStart,
                    'end_date' => $periodEnd,
                    'is_active' => true,
                    'is_current' => $i === 1, // Premier trimestre par défaut
                ];
            }
        } else { // semestre
            // Division en 2 semestres
            $periodDays = intval($totalDays / 2);
            
            for ($i = 1; $i <= 2; $i++) {
                $periodStart = $i === 1 ? $startDate : $startDate->copy()->addDays($periodDays);
                $periodEnd = $i === 2 ? $endDate : $startDate->copy()->addDays($periodDays - 1);
                
                $periods[] = [
                    'name' => "Semestre $i",
                    'short_name' => "S$i",
                    'order' => $i,
                    'start_date' => $periodStart,
                    'end_date' => $periodEnd,
                    'is_active' => true,
                    'is_current' => $i === 1, // Premier semestre par défaut
                ];
            }
        }

        return $periods;
    }
}