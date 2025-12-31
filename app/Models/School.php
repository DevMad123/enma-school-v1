<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'type',
        'educational_levels',
        'academic_structure_config',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'logo_path',
        'stamp_path',
        'signature_path',
        'academic_system',
        'grading_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'educational_levels' => 'array',
            'academic_structure_config' => 'array',
        ];
    }

    /**
     * Relation avec les paramètres de l'école
     */
    public function settings()
    {
        return $this->hasMany(SchoolSetting::class);
    }

    /**
     * Relation avec les années académiques
     */
    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    /**
     * Relations MODULE A3 - Structure académique
     */
    public function cycles()
    {
        return $this->hasMany(Cycle::class);
    }

    public function levels()
    {
        return $this->hasMany(Level::class);
    }

    public function academicTracks()
    {
        return $this->hasMany(AcademicTrack::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    /**
     * Relations UNIVERSITAIRES - Structure universitaire
     */
    public function ufrs()
    {
        return $this->hasMany(UFR::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }

    public function courseUnits()
    {
        return $this->hasMany(CourseUnit::class);
    }

    /**
     * Obtenir l'année académique active de cette école
     */
    public function getActiveAcademicYear()
    {
        return $this->academicYears()->where('is_active', true)->first();
    }

    /**
     * Obtenir l'année académique courante de cette école
     */
    public function getCurrentAcademicYear()
    {
        return $this->academicYears()->where('is_current', true)->first();
    }

    /**
     * Obtenir une valeur de paramètre spécifique
     */
    public function getSetting(string $key, $default = null)
    {
        $setting = $this->settings()->where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Définir une valeur de paramètre
     */
    public function setSetting(string $key, $value)
    {
        return $this->settings()->updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

    /**
     * Scope pour obtenir l'école active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtenir l'école active (il n'y en a qu'une pour l'instant)
     */
    public static function getActiveSchool()
    {
        return static::active()->first();
    }

    /**
     * MODULE A5 - Paramètres pédagogiques
     * Obtenir le seuil de validation pour un niveau spécifique
     */
    public function getLevelValidationThreshold(Level $level)
    {
        return $this->getSetting("level_{$level->id}_validation_threshold", $this->getSetting('validation_threshold', '10'));
    }

    /**
     * MODULE A5 - Paramètres pédagogiques
     * Obtenir le seuil de validation pour une matière spécifique
     */
    public function getSubjectValidationThreshold(Subject $subject)
    {
        return $this->getSetting("subject_{$subject->id}_validation_threshold", $this->getSetting('validation_threshold', '10'));
    }

    /**
     * MODULE A5 - Paramètres pédagogiques
     * Obtenir tous les paramètres pédagogiques
     */
    public function getPedagogicalSettings()
    {
        return [
            'validation_threshold' => $this->getSetting('validation_threshold', '10'),
            'redoublement_threshold' => $this->getSetting('redoublement_threshold', '8'),
            'bulletin_footer_text' => $this->getSetting('bulletin_footer_text', ''),
            'automatic_promotion' => $this->getSetting('automatic_promotion', 'false'),
            'mention_system' => $this->getSetting('mention_system', 'true'),
            'validation_subjects_required' => $this->getSetting('validation_subjects_required', '80'),
        ];
    }

    /**
     * NOUVEAUX TYPES - Méthodes pour gérer les types d'établissements flexibles
     */
    
    /**
     * Vérifier si l'établissement est pré-universitaire
     */
    public function isPreUniversity(): bool
    {
        return $this->type === 'pre_university';
    }

    /**
     * Vérifier si l'établissement est universitaire
     */
    public function isUniversity(): bool
    {
        return $this->type === 'university';
    }

    /**
     * Vérifier si l'établissement gère un niveau éducatif spécifique
     */
    public function hasLevel(string $level): bool
    {
        $levels = $this->educational_levels ?? [];
        return in_array($level, $levels);
    }

    /**
     * Vérifier si l'établissement gère le niveau primaire
     */
    public function hasPrimary(): bool
    {
        return $this->hasLevel('primary');
    }

    /**
     * Vérifier si l'établissement gère le niveau secondaire
     */
    public function hasSecondary(): bool
    {
        return $this->hasLevel('secondary');
    }

    /**
     * Vérifier si l'établissement gère le niveau technique
     */
    public function hasTechnical(): bool
    {
        return $this->hasLevel('technical');
    }

    /**
     * Obtenir les cycles disponibles selon le type d'établissement
     */
    public function getAvailableCycles(): array
    {
        if ($this->isUniversity()) {
            return [
                'licence' => 'Licence (L1, L2, L3)',
                'master' => 'Master (M1, M2)',
                'doctorat' => 'Doctorat (D1, D2, D3)',
                'cycles_courts' => 'Cycles courts (BTS, DUT, IUT)'
            ];
        }
        
        $cycles = [];
        if ($this->hasPrimary()) {
            $cycles['primaire'] = 'Primaire (CP1 à CM2)';
        }
        if ($this->hasSecondary()) {
            $cycles['secondaire'] = 'Secondaire (6e à Terminale)';
        }
        if ($this->hasTechnical()) {
            $cycles['technique'] = 'Technique et Professionnel';
        }
        
        return $cycles;
    }

    /**
     * Obtenir la structure académique recommandée selon le type
     */
    public function getRecommendedStructure(): array
    {
        if ($this->isUniversity()) {
            return [
                'hierarchy' => ['ufr', 'department', 'program', 'semester'],
                'evaluation_system' => 'credits',
                'academic_period' => 'semester',
                'grading_scale' => 'ects'
            ];
        }
        
        return [
            'hierarchy' => ['cycle', 'level', 'class'],
            'evaluation_system' => 'notes',
            'academic_period' => $this->academic_system ?? 'trimestre',
            'grading_scale' => $this->grading_system ?? '20'
        ];
    }

    /**
     * Obtenir le libellé du type d'établissement
     */
    public function getTypeLabel(): string
    {
        return match($this->type) {
            'pre_university' => 'Pré-universitaire',
            'university' => 'Universitaire',
            default => 'Non défini'
        };
    }

    /**
     * Obtenir les niveaux éducatifs configurés avec leurs libellés
     */
    public function getEducationalLevelsWithLabels(): array
    {
        $levels = $this->educational_levels ?? [];
        $labels = [
            'primary' => 'Primaire',
            'secondary' => 'Secondaire', 
            'technical' => 'Technique/Professionnel'
        ];
        
        return array_intersect_key($labels, array_flip($levels));
    }
}
