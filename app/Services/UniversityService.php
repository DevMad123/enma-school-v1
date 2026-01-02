<?php

namespace App\Services;

use App\Models\School;
use App\Models\UFR;
use App\Models\Department;
use App\Models\Program;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Exceptions\BusinessRuleException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion des entités universitaires
 * 
 * Ce service centralise la logique métier pour :
 * - Gestion des UFR, départements, programmes, semestres et unités d'enseignement
 * - Calculs statistiques universitaires
 * - Validation des règles métier
 * - Opérations CRUD avec vérifications de dépendances
 * 
 * @package App\Services
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 */
class UniversityService
{
    /**
     * Obtenir les statistiques complètes pour le tableau de bord universitaire
     * 
     * @param School $school L'école pour laquelle calculer les statistiques
     * @return array<string, mixed> Tableau associatif des statistiques
     * 
     * @throws \Exception Si l'école n'est pas configurée pour l'université
     */
    public function getDashboardStats(School $school): array
    {
        if (!$school->isUniversity()) {
            throw new \Exception('Cette école n\'est pas configurée comme université.');
        }
        
        return [
            'ufrs' => UFR::where('school_id', $school->id)->active()->count(),
            'departments' => Department::where('school_id', $school->id)->active()->count(),
            'programs' => Program::where('school_id', $school->id)->active()->count(),
            'current_semester' => Semester::current()->count(),
            'total_course_units' => $this->getTotalCourseUnits($school),
            'total_credits' => $this->getTotalCredits($school),
            'active_academic_years' => $this->getActiveAcademicYearsCount($school)
        ];
    }
    
    /**
     * Calculer les statistiques détaillées pour une UFR
     * 
     * @param UFR $ufr L'UFR pour laquelle calculer les statistiques
     * @return array<string, int> Statistiques de l'UFR
     */
    public function getUFRStats(UFR $ufr): array
    {
        $ufr->load(['departments.programs.semesters.courseUnits']);
        
        $totalPrograms = $ufr->departments->sum(function ($department) {
            return $department->programs->count();
        });
        
        $totalSemesters = $ufr->departments->flatMap(function ($department) {
            return $department->programs->flatMap->semesters;
        })->count();
        
        $totalCourseUnits = $ufr->departments->flatMap(function ($department) {
            return $department->programs->flatMap(function ($program) {
                return $program->semesters->flatMap->courseUnits;
            });
        })->count();
        
        return [
            'total_departments' => $ufr->departments->count(),
            'total_programs' => $totalPrograms,
            'total_semesters' => $totalSemesters,
            'total_course_units' => $totalCourseUnits,
            'active_departments' => $ufr->departments->where('is_active', true)->count()
        ];
    }
    
    /**
     * Calculer les statistiques pour un département
     * 
     * @param Department $department Le département pour lequel calculer les statistiques
     * @return array<string, int> Statistiques du département
     */
    public function getDepartmentStats(Department $department): array
    {
        $department->load(['programs.semesters.courseUnits']);
        
        $totalSemesters = $department->programs->sum(function ($program) {
            return $program->semesters->count();
        });
        
        $totalCourseUnits = $department->programs->flatMap(function ($program) {
            return $program->semesters->flatMap->courseUnits;
        })->count();
        
        return [
            'total_programs' => $department->programs->count(),
            'total_semesters' => $totalSemesters,
            'total_course_units' => $totalCourseUnits,
            'active_programs' => $department->programs->where('is_active', true)->count()
        ];
    }
    
    /**
     * Calculer les statistiques pour un programme
     * 
     * @param Program $program Le programme pour lequel calculer les statistiques
     * @return array<string, mixed> Statistiques du programme
     */
    public function getProgramStats(Program $program): array
    {
        $program->load(['semesters.courseUnits']);
        
        $totalCourseUnits = $program->semesters->sum(function ($semester) {
            return $semester->courseUnits->count();
        });
        
        $totalCredits = $program->semesters->sum(function ($semester) {
            return $semester->courseUnits->sum('credits');
        });
        
        $averageCreditsPerSemester = $program->semesters->count() > 0 
            ? round($totalCredits / $program->semesters->count(), 2) 
            : 0;
        
        return [
            'total_semesters' => $program->semesters->count(),
            'total_course_units' => $totalCourseUnits,
            'total_credits' => $totalCredits,
            'average_credits_per_semester' => $averageCreditsPerSemester,
            'completion_rate' => $this->calculateProgramCompletionRate($program)
        ];
    }
    
    /**
     * Valider qu'une UFR peut être supprimée
     * 
     * @param UFR $ufr L'UFR à supprimer
     * @return bool True si la suppression est possible
     * 
     * @throws BusinessRuleException Si la suppression n'est pas possible
     */
    public function validateUFRDeletion(UFR $ufr): bool
    {
        if ($ufr->departments()->count() > 0) {
            throw new BusinessRuleException(
                'Impossible de supprimer cette UFR car elle contient des départements.',
                0,
                null,
                'UFR_HAS_DEPARTMENTS',
                ['ufr_id' => $ufr->id, 'departments_count' => $ufr->departments()->count()]
            );
        }
        
        return true;
    }
    
    /**
     * Valider qu'un département peut être supprimé
     * 
     * @param Department $department Le département à supprimer
     * @return bool True si la suppression est possible
     * 
     * @throws BusinessRuleException Si la suppression n'est pas possible
     */
    public function validateDepartmentDeletion(Department $department): bool
    {
        if ($department->programs()->count() > 0) {
            throw new BusinessRuleException(
                'Impossible de supprimer ce département car il contient des programmes.',
                0,
                null,
                'DEPARTMENT_HAS_PROGRAMS',
                ['department_id' => $department->id, 'programs_count' => $department->programs()->count()]
            );
        }
        
        return true;
    }
    
    /**
     * Valider qu'un programme peut être supprimé
     * 
     * @param Program $program Le programme à supprimer
     * @return bool True si la suppression est possible
     * 
     * @throws BusinessRuleException Si la suppression n'est pas possible
     */
    public function validateProgramDeletion(Program $program): bool
    {
        if ($program->semesters()->count() > 0) {
            throw new BusinessRuleException(
                'Impossible de supprimer ce programme car il contient des semestres.',
                0,
                null,
                'PROGRAM_HAS_SEMESTERS',
                ['program_id' => $program->id, 'semesters_count' => $program->semesters()->count()]
            );
        }
        
        return true;
    }
    
    /**
     * Valider qu'un semestre peut être supprimé
     * 
     * @param Semester $semester Le semestre à supprimer
     * @return bool True si la suppression est possible
     * 
     * @throws BusinessRuleException Si la suppression n'est pas possible
     */
    public function validateSemesterDeletion(Semester $semester): bool
    {
        if ($semester->courseUnits()->count() > 0) {
            throw new BusinessRuleException(
                'Impossible de supprimer ce semestre car il contient des unités d\'enseignement.',
                0,
                null,
                'SEMESTER_HAS_COURSE_UNITS',
                ['semester_id' => $semester->id, 'course_units_count' => $semester->courseUnits()->count()]
            );
        }
        
        return true;
    }
    
    /**
     * Traiter les objectifs depuis un texte multiligne
     * 
     * @param string|null $objectivesText Texte des objectifs (une ligne par objectif)
     * @return array<int, string>|null Tableau d'objectifs ou null
     */
    public function parseObjectives(?string $objectivesText): ?array
    {
        if (empty($objectivesText)) {
            return null;
        }
        
        $objectives = array_filter(
            array_map('trim', explode("\n", $objectivesText)),
            function($line) { return !empty($line); }
        );
        
        return !empty($objectives) ? $objectives : null;
    }
    
    /**
     * Obtenir le niveau académique selon le niveau d'études et le numéro de semestre
     * 
     * @param string $level Niveau d'études (licence, master, doctorat, etc.)
     * @param int $semesterNumber Numéro du semestre
     * @return int Niveau académique (1-8)
     */
    public function getAcademicLevel(string $level, int $semesterNumber): int
    {
        switch (strtolower($level)) {
            case 'licence':
                return ceil($semesterNumber / 2); // L1: 1-2, L2: 3-4, L3: 5-6
                
            case 'master':
                return 3 + ceil($semesterNumber / 2); // M1: 1-2 (niveau 4), M2: 3-4 (niveau 5)
                
            case 'doctorat':
                return 5 + ceil($semesterNumber / 2); // D1+: niveau 6+
                
            case 'dut':
            case 'bts':
                return ceil($semesterNumber / 2); // Niveau post-bac : 1-2
                
            default:
                return min($semesterNumber, 8);
        }
    }
    
    /**
     * Calculer les dates de début et fin d'un semestre selon l'année académique
     * 
     * @param int $semesterNumber Numéro du semestre
     * @param \App\Models\AcademicYear|null $academicYear Année académique
     * @return array<string, \DateTime> Dates de début et fin
     */
    public function calculateSemesterDates(int $semesterNumber, $academicYear = null): array
    {
        if (!$academicYear) {
            $academicYear = \App\Models\AcademicYear::current()->first();
        }
        
        if (!$academicYear) {
            throw new \Exception('Aucune année académique active trouvée.');
        }
        
        $startDate = \Carbon\Carbon::parse($academicYear->start_date);
        $endDate = \Carbon\Carbon::parse($academicYear->end_date);
        
        // Calculer la durée totale en jours
        $totalDays = $startDate->diffInDays($endDate);
        $semesterDuration = intval($totalDays / 2);
        
        if ($semesterNumber % 2 === 1) {
            // Semestre impair (1er semestre)
            $semesterStart = $startDate->copy();
            $semesterEnd = $startDate->copy()->addDays($semesterDuration);
        } else {
            // Semestre pair (2ème semestre)
            $semesterStart = $startDate->copy()->addDays($semesterDuration);
            $semesterEnd = $endDate->copy();
        }
        
        return [
            'start_date' => $semesterStart,
            'end_date' => $semesterEnd
        ];
    }
    
    /**
     * Supprimer une entité universitaire avec validation et logging
     * 
     * @param object $entity L'entité à supprimer (UFR, Department, Program, etc.)
     * @param string $entityType Type d'entité pour le logging
     * @return bool True si la suppression a réussi
     * 
     * @throws BusinessRuleException Si la suppression n'est pas possible
     * @throws \Exception Si une erreur se produit lors de la suppression
     */
    public function deleteUniversityEntity($entity, string $entityType): bool
    {
        try {
            DB::beginTransaction();
            
            // Validation selon le type d'entité
            switch ($entityType) {
                case 'ufr':
                    $this->validateUFRDeletion($entity);
                    break;
                case 'department':
                    $this->validateDepartmentDeletion($entity);
                    break;
                case 'program':
                    $this->validateProgramDeletion($entity);
                    break;
                case 'semester':
                    $this->validateSemesterDeletion($entity);
                    break;
            }
            
            // Sauvegarder les informations avant suppression
            $entityData = $entity->toArray();
            $entityName = $entity->name ?? $entity->title ?? 'Entité inconnue';
            
            // Suppression
            $entity->delete();
            
            // Logging
            try {
                if (class_exists(\Spatie\Activitylog\Models\Activity::class)) {
                    \Spatie\Activitylog\Models\Activity::create([
                        'log_name' => 'university_management',
                        'description' => "Suppression {$entityType}: {$entityName}",
                        'causer_type' => auth()->check() ? get_class(auth()->user()) : null,
                        'causer_id' => auth()->id(),
                        'properties' => [
                            'entity_type' => $entityType,
                            'entity_data' => $entityData,
                            'entity_name' => $entityName
                        ]
                    ]);
                } else {
                    Log::info("Suppression {$entityType}: {$entityName}", [
                        'entity_type' => $entityType,
                        'entity_data' => $entityData,
                        'entity_name' => $entityName,
                        'user_id' => auth()->id()
                    ]);
                }
            } catch (\Exception $e) {
                Log::warning('Erreur lors du logging de suppression', [
                    'error' => $e->getMessage(),
                    'entity_type' => $entityType
                ]);
            }
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erreur lors de la suppression {$entityType}", [
                'entity_id' => $entity->id ?? null,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Obtenir le nombre total d'unités d'enseignement pour une école
     */
    private function getTotalCourseUnits(School $school): int
    {
        return CourseUnit::whereHas('semester.program.department.ufr', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->count();
    }
    
    /**
     * Obtenir le nombre total de crédits pour une école
     */
    private function getTotalCredits(School $school): int
    {
        return CourseUnit::whereHas('semester.program.department.ufr', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->sum('credits');
    }
    
    /**
     * Obtenir le nombre d'années académiques actives
     */
    private function getActiveAcademicYearsCount(School $school): int
    {
        return \App\Models\AcademicYear::where('is_active', true)->count();
    }
    
    /**
     * Calculer le taux de completion d'un programme
     */
    private function calculateProgramCompletionRate(Program $program): float
    {
        $expectedSemesters = $program->duration_semesters;
        $actualSemesters = $program->semesters->count();
        
        if ($expectedSemesters === 0) {
            return 0.0;
        }
        
        return min(round(($actualSemesters / $expectedSemesters) * 100, 2), 100.0);
    }
}