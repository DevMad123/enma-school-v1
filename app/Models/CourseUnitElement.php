<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasSchoolContext;

class CourseUnitElement extends Model
{
    use HasSchoolContext;

    protected $fillable = [
        'course_unit_id',
        'code',
        'name',
        'description',
        'credits',
        'coefficient',
        'hours_cm',
        'hours_td',
        'hours_tp',
        'hours_total',
        'evaluation_type',
        'status',
    ];

    protected $casts = [
        'credits' => 'integer',
        'coefficient' => 'decimal:2',
        'hours_cm' => 'integer',
        'hours_td' => 'integer',
        'hours_tp' => 'integer',
        'hours_total' => 'integer',
        'status' => 'string',
    ];

    /**
     * Boot method pour auto-calcul des heures
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($element) {
            $element->hours_total = $element->hours_cm + $element->hours_td + $element->hours_tp;
        });
        
        static::saved(function ($element) {
            // Synchroniser l'UE parente après sauvegarde
            $element->courseUnit->syncFromElements();
        });
        
        static::deleted(function ($element) {
            // Synchroniser l'UE parente après suppression
            $element->courseUnit->syncFromElements();
        });
    }

    /**
     * Relation avec l'unité d'enseignement
     */
    public function courseUnit(): BelongsTo
    {
        return $this->belongsTo(CourseUnit::class);
    }

    /**
     * Scope pour les ECUE actifs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope pour les ECUE par type d'évaluation
     */
    public function scopeByEvaluationType($query, $type)
    {
        return $query->where('evaluation_type', $type);
    }

    /**
     * Scope pour les ECUE avec contrôle continu
     */
    public function scopeWithControleContinu($query)
    {
        return $query->whereIn('evaluation_type', ['controle_continu', 'mixte']);
    }

    /**
     * Accesseur pour obtenir le nom complet avec code
     */
    public function getFullNameAttribute(): string
    {
        return $this->code . ' - ' . $this->name;
    }

    /**
     * Accesseur pour obtenir les crédits pondérés par le coefficient
     */
    public function getCreditsWeightedAttribute(): float
    {
        return $this->credits * $this->coefficient;
    }

    /**
     * Accesseur pour vérifier si l'ECUE est actif
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Accesseur pour obtenir le type d'évaluation formaté
     */
    public function getEvaluationTypeFormattedAttribute(): string
    {
        return match($this->evaluation_type) {
            'controle_continu' => 'Contrôle continu',
            'examen_final' => 'Examen final',
            'mixte' => 'Mixte (CC + Examen)',
            default => $this->evaluation_type
        };
    }

    /**
     * Obtenir la répartition des heures
     */
    public function getHoursDistributionAttribute(): array
    {
        $total = $this->hours_total;
        
        if ($total == 0) {
            return ['cm' => 0, 'td' => 0, 'tp' => 0];
        }
        
        return [
            'cm' => round(($this->hours_cm / $total) * 100, 1),
            'td' => round(($this->hours_td / $total) * 100, 1),
            'tp' => round(($this->hours_tp / $total) * 100, 1),
        ];
    }

    /**
     * Relation avec les évaluations (vide car ECUE n'a pas d'évaluations directes)
     */
    public function evaluations()
    {
        // Dans le système LMD, les évaluations sont liées aux UE, pas aux ECUE
        // Retourne un query builder vide pour supporter exists()
        return \App\Models\Evaluation::whereRaw('1 = 0');
    }

    /**
     * Relation avec les notes (vide car ECUE n'a pas de notes directes)
     */
    public function grades()
    {
        // Dans le système LMD, les notes sont liées aux UE, pas aux ECUE
        // Retourne un query builder vide pour supporter exists()
        return \App\Models\Grade::whereRaw('1 = 0');
    }

    /**
     * Vérifier si l'ECUE a des évaluations
     */
    public function hasEvaluations(): bool
    {
        // Les évaluations sont liées à l'UE parente, pas directement aux ECUE
        return false;
    }

    /**
     * Vérifier si l'ECUE peut être supprimé
     */
    public function canBeDeleted(): bool
    {
        // Un ECUE peut être supprimé tant qu'il n'est pas référencé ailleurs
        // Les évaluations et notes sont liées à l'UE, pas aux ECUE
        return true;
    }

    /**
     * Obtenir la couleur du statut
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            default => 'blue'
        };
    }
}
