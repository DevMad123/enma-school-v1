<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Modèle spécialisé pour les étudiants pré-universitaires
 * 
 * Gère les données spécifiques aux élèves du primaire, collège et lycée
 * Relation polymorphique avec UnifiedStudent
 */
class PreUniversityStudent extends Model
{
    protected $fillable = [
        'student_matricule',
        'academic_serie',
        'is_repeater',
        'repeat_count',
        'previous_school',
        'entry_year',
        'parent_profile_id',
        'father_name',
        'mother_name',
        'guardian_name',
        'guardian_phone',
        'medical_conditions',
        'special_needs',
        'has_transport',
        'transport_route',
        'conduct_points',
        'behavioral_notes',
        'yearly_average',
        'class_rank',
        'total_absences',
        'tardiness_count',
    ];

    protected $casts = [
        'entry_year' => 'integer',
        'is_repeater' => 'boolean',
        'repeat_count' => 'integer',
        'has_transport' => 'boolean',
        'conduct_points' => 'integer',
        'yearly_average' => 'decimal:2',
        'class_rank' => 'integer',
        'total_absences' => 'integer',
        'tardiness_count' => 'integer',
    ];

    /**
     * Relation polymorphique inverse vers UnifiedStudent
     */
    public function student(): MorphOne
    {
        return $this->morphOne(UnifiedStudent::class, 'studentable');
    }

    /**
     * Relation avec le profil parental
     */
    public function parentProfile(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class);
    }

    /**
     * Accessor pour les données personnelles via student
     */
    public function getPersonalDataAttribute()
    {
        return $this->student?->person;
    }

    /**
     * Relation avec les inscriptions préuniversitaires
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(PreUniversityEnrollment::class, 'unified_student_id', 'student_id')
                   ->whereHas('unifiedStudent', function($query) {
                       $query->where('studentable_type', static::class);
                   });
    }

    /**
     * Générer un matricule élève
     */
    public static function generateMatricule(int $schoolId): string
    {
        $year = now()->year;
        $lastNumber = static::whereHas('student.person', function($query) use ($schoolId) {
                               $query->where('school_id', $schoolId);
                           })
                           ->where('student_matricule', 'like', "ELV{$year}%")
                           ->count() + 1;
        
        return sprintf('ELV%d%05d', $year, $lastNumber);
    }

    /**
     * Scope pour les redoublants
     */
    public function scopeRepeaters($query)
    {
        return $query->where('is_repeater', true);
    }

    /**
     * Scope par série académique
     */
    public function scopeBySeries($query, string $serie)
    {
        return $query->where('academic_serie', $serie);
    }

    /**
     * Scope pour élèves avec besoins spéciaux
     */
    public function scopeWithSpecialNeeds($query)
    {
        return $query->whereNotNull('special_needs');
    }

    /**
     * Scope pour élèves avec transport
     */
    public function scopeWithTransport($query)
    {
        return $query->where('has_transport', true);
    }

    /**
     * Calculer l'âge en fonction de l'année d'entrée
     */
    public function getSchoolAgeAttribute(): int
    {
        return now()->year - $this->entry_year;
    }

    /**
     * Vérifier si l'élève est en difficulté disciplinaire
     */
    public function isDisciplinaryRisk(): bool
    {
        return $this->conduct_points < 10; // Seuil critique
    }

    /**
     * Vérifier si l'élève a des problèmes d'assiduité
     */
    public function hasAttendanceIssues(): bool
    {
        return $this->total_absences > 20 || $this->tardiness_count > 15;
    }

    /**
     * Calculer le pourcentage de présence
     */
    public function getAttendancePercentage(int $totalSchoolDays = 180): float
    {
        $presentDays = $totalSchoolDays - $this->total_absences;
        return $presentDays > 0 ? round(($presentDays / $totalSchoolDays) * 100, 2) : 0;
    }

    /**
     * Obtenir l'appréciation comportementale
     */
    public function getBehaviorAppreciationAttribute(): string
    {
        return match(true) {
            $this->conduct_points >= 18 => 'Excellent',
            $this->conduct_points >= 15 => 'Très bien',
            $this->conduct_points >= 12 => 'Bien',
            $this->conduct_points >= 10 => 'Assez bien',
            $this->conduct_points >= 7 => 'Passable',
            default => 'Insuffisant'
        };
    }

    /**
     * Obtenir l'appréciation académique
     */
    public function getAcademicAppreciationAttribute(): string
    {
        if (!$this->yearly_average) {
            return 'Non évalué';
        }

        return match(true) {
            $this->yearly_average >= 18 => 'Excellent',
            $this->yearly_average >= 16 => 'Très bien',
            $this->yearly_average >= 14 => 'Bien',
            $this->yearly_average >= 12 => 'Assez bien',
            $this->yearly_average >= 10 => 'Passable',
            $this->yearly_average >= 8 => 'Insuffisant',
            default => 'Médiocre'
        };
    }

    /**
     * Prédiction de passage (basée sur moyenne et comportement)
     */
    public function predictPromotion(): array
    {
        $academicScore = $this->yearly_average ?? 0;
        $behaviorScore = $this->conduct_points;
        $attendanceScore = $this->getAttendancePercentage();

        // Algorithme de prédiction simplifié
        $overallScore = ($academicScore * 0.7) + (($behaviorScore / 20) * 10 * 0.15) + (($attendanceScore / 100) * 10 * 0.15);

        $prediction = match(true) {
            $overallScore >= 12 => 'Passage recommandé',
            $overallScore >= 10 => 'Passage conditionnel',
            $overallScore >= 8 => 'Redoublement probable',
            default => 'Redoublement recommandé'
        };

        return [
            'score' => round($overallScore, 2),
            'prediction' => $prediction,
            'factors' => [
                'academic' => $academicScore,
                'behavior' => $behaviorScore,
                'attendance' => round($attendanceScore, 2)
            ]
        ];
    }

    /**
     * Mettre à jour les statistiques de fin d'année
     */
    public function updateYearlyStats(float $average, int $rank): void
    {
        $this->update([
            'yearly_average' => $average,
            'class_rank' => $rank
        ]);
    }

    /**
     * Ajouter une absence
     */
    public function addAbsence(bool $excused = false): void
    {
        $this->increment('total_absences');
        
        // Log dans student_metadata si necessaire
        $student = $this->student;
        if ($student) {
            $metadata = $student->student_metadata ?? [];
            $metadata['last_absence'] = now()->toDateString();
            $metadata['last_absence_excused'] = $excused;
            $student->update(['student_metadata' => $metadata]);
        }
    }

    /**
     * Ajouter un retard
     */
    public function addTardiness(): void
    {
        $this->increment('tardiness_count');
    }

    /**
     * Modifier les points de conduite
     */
    public function adjustConductPoints(int $points, string $reason = ''): void
    {
        $newPoints = max(0, min(20, $this->conduct_points + $points));
        
        $currentNotes = $this->behavioral_notes ?? '';
        $pointsText = $points >= 0 ? "+{$points}" : "{$points}";
        $newNote = now()->format('Y-m-d') . ": {$reason} ({$pointsText} points)";
        $updatedNotes = $currentNotes ? $currentNotes . "\n" . $newNote : $newNote;
        
        $this->update([
            'conduct_points' => $newPoints,
            'behavioral_notes' => $updatedNotes
        ]);
    }
}