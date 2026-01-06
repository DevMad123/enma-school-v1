<?php

namespace App\Traits;

use App\Models\UnifiedStudent;
use App\Models\UnifiedEvaluation;
use App\Models\UnifiedGrade;
use App\Models\EducationalSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait pour les relations communes du contexte éducatif unifié
 * 
 * Fournit des méthodes communes pour les modèles qui interagissent
 * avec le système éducatif unifié (School, Level, Semester, etc.)
 */
trait HasEducationalContext
{
    /**
     * Relation avec les étudiants unifiés
     */
    public function unifiedStudents(): HasMany
    {
        return $this->hasMany(UnifiedStudent::class, $this->getEducationalContextForeignKey());
    }

    /**
     * Relation avec les matières/UE éducatives
     */
    public function educationalSubjects(): HasMany
    {
        return $this->hasMany(EducationalSubject::class, $this->getEducationalContextForeignKey())
                   ->where('educational_context', $this->getEducationalContextType());
    }

    /**
     * Relation avec les évaluations unifiées
     */
    public function unifiedEvaluations(): HasMany
    {
        return $this->hasMany(UnifiedEvaluation::class, $this->getEducationalContextForeignKey())
                   ->where('educational_context', $this->getEducationalContextType());
    }

    /**
     * Obtenir le type de contexte éducatif
     */
    abstract protected function getEducationalContextType(): string;

    /**
     * Obtenir la clé étrangère pour le contexte éducatif
     */
    protected function getEducationalContextForeignKey(): string
    {
        return 'school_id';
    }

    /**
     * Scope pour filtrer par contexte éducatif
     */
    public function scopeByEducationalContext($query, string $context)
    {
        return $query->where('educational_context', $context);
    }

    /**
     * Obtenir les statistiques étudiants pour ce contexte
     */
    public function getStudentStats(): array
    {
        $students = $this->unifiedStudents()->with('studentable');
        
        return [
            'total' => $students->count(),
            'active' => $students->active()->count(),
            'preuniversity' => $students->byEducationalContext('preuniversity')->count(),
            'university' => $students->byEducationalContext('university')->count(),
        ];
    }

    /**
     * Obtenir les matières actives pour ce contexte
     */
    public function getActiveSubjects()
    {
        return $this->educationalSubjects()
                   ->active()
                   ->ordered()
                   ->get();
    }

    /**
     * Obtenir les évaluations récentes
     */
    public function getRecentEvaluations(int $limit = 10)
    {
        return $this->unifiedEvaluations()
                   ->with(['educationalSubject', 'creator'])
                   ->orderBy('evaluation_date', 'desc')
                   ->limit($limit)
                   ->get();
    }
}