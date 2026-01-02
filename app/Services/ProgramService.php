<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Department;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Models\School;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service pour la gestion des programmes universitaires
 * 
 * Ce service centralise la logique métier pour :
 * - Gestion des programmes et leurs statistiques
 * - Validation des règles métier pour programmes
 * - Opérations CRUD avec gestion des dépendances
 * - Calculs et validations spécifiques aux programmes
 * 
 * @package App\Services
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 */
class ProgramService
{
    /**
     * Obtenir tous les programmes avec leurs statistiques
     * 
     * @return \Illuminate\Database\Eloquent\Collection Collection de programmes
     */
    public function getAllProgramsWithStats()
    {
        return Program::with(['department.ufr', 'school'])
            ->withCount([
                'semesters',
                'semesters as active_semesters_count' => function ($query) {
                    $query->where('is_active', true);
                }
            ])
            ->orderBy('level')
            ->orderBy('name')
            ->get()
            ->map(function ($program) {
                return $this->enrichProgramWithStats($program);
            });
    }

    /**
     * Enrichir un programme avec ses statistiques détaillées
     * 
     * @param Program $program Programme à enrichir
     * @return Program Programme enrichi
     */
    public function enrichProgramWithStats(Program $program): Program
    {
        // Charger les relations nécessaires si pas déjà fait
        $program->loadMissing(['semesters.courseUnits']);
        
        // Calculer les statistiques
        $totalCourseUnits = $program->semesters->sum(function ($semester) {
            return $semester->courseUnits->count();
        });
        
        $totalCredits = $program->semesters->sum(function ($semester) {
            return $semester->courseUnits->sum('credits');
        });
        
        $totalHours = $program->semesters->sum(function ($semester) {
            return $semester->courseUnits->sum('hours_total');
        });
        
        // Ajouter les statistiques comme attributs
        $program->setAttribute('total_course_units', $totalCourseUnits);
        $program->setAttribute('calculated_total_credits', $totalCredits);
        $program->setAttribute('total_hours', $totalHours);
        $program->setAttribute('completion_rate', $this->calculateCompletionRate($program));
        
        return $program;
    }

    /**
     * Calculer le taux de complétion d'un programme
     * 
     * @param Program $program Programme à analyser
     * @return float Taux de complétion (0-100)
     */
    public function calculateCompletionRate(Program $program): float
    {
        if ($program->duration_semesters == 0 || $program->total_credits == 0) {
            return 0.0;
        }
        
        // Charger les semestres si pas déjà fait
        $program->loadMissing('semesters.courseUnits');
        
        $activeSemesters = $program->semesters->where('is_active', true)->count();
        $expectedSemesters = $program->duration_semesters;
        
        $actualCredits = $program->semesters->sum(function ($semester) {
            return $semester->courseUnits->sum('credits');
        });
        $expectedCredits = $program->total_credits;
        
        // Calculer basé sur les semestres ET les crédits
        $semesterCompletion = min(100, ($activeSemesters / $expectedSemesters) * 100);
        $creditCompletion = min(100, ($actualCredits / $expectedCredits) * 100);
        
        // Retourner la moyenne pondérée
        return round(($semesterCompletion + $creditCompletion) / 2, 1);
    }

    /**
     * Créer un nouveau programme avec validation
     * 
     * @param array $data Données du programme
     * @return Program Programme créé
     * @throws BusinessRuleException Si validation échoue
     */
    public function createProgram(array $data): Program
    {
        try {
            DB::beginTransaction();
            
            // Validation métier spécifique
            $this->validateProgramData($data);
            
            // Traiter les objectifs depuis le texte
            if (isset($data['objectives_text']) && !empty($data['objectives_text'])) {
                $objectives = array_filter(
                    array_map('trim', explode("\n", $data['objectives_text'])),
                    function($line) { return !empty($line); }
                );
                $data['objectives'] = $objectives;
            }
            unset($data['objectives_text']);
            
            // Ajouter l'école active
            $data['school_id'] = School::getActiveSchool()->id;
            
            // Créer le programme
            $program = Program::create($data);
            
            // Logger la création
            Log::info('Programme créé', [
                'program_id' => $program->id,
                'program_name' => $program->name,
                'department_id' => $program->department_id,
                'created_by' => auth()->id()
            ]);
            
            DB::commit();
            return $program;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création programme', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un programme avec validation
     * 
     * @param Program $program Programme à mettre à jour
     * @param array $data Nouvelles données
     * @return Program Programme mis à jour
     * @throws BusinessRuleException Si validation échoue
     */
    public function updateProgram(Program $program, array $data): Program
    {
        try {
            DB::beginTransaction();
            
            // Validation métier spécifique
            $this->validateProgramData($data, $program->id);
            
            // Traiter les objectifs depuis le texte
            if (isset($data['objectives_text'])) {
                if (!empty($data['objectives_text'])) {
                    $objectives = array_filter(
                        array_map('trim', explode("\n", $data['objectives_text'])),
                        function($line) { return !empty($line); }
                    );
                    $data['objectives'] = $objectives;
                } else {
                    $data['objectives'] = null;
                }
            }
            unset($data['objectives_text']);
            
            // Mettre à jour
            $program->update($data);
            
            // Logger la mise à jour
            Log::info('Programme mis à jour', [
                'program_id' => $program->id,
                'program_name' => $program->name,
                'updated_by' => auth()->id()
            ]);
            
            DB::commit();
            return $program->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour programme', [
                'program_id' => $program->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un programme avec vérifications
     * 
     * @param Program $program Programme à supprimer
     * @throws BusinessRuleException Si suppression non autorisée
     */
    public function deleteProgram(Program $program): void
    {
        try {
            DB::beginTransaction();
            
            // Vérifier les dépendances
            $this->validateProgramDeletion($program);
            
            $programId = $program->id;
            $programName = $program->name;
            
            // Supprimer le programme
            $program->delete();
            
            // Logger la suppression
            Log::info('Programme supprimé', [
                'program_id' => $programId,
                'program_name' => $programName,
                'deleted_by' => auth()->id()
            ]);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression programme', [
                'program_id' => $program->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Valider les données d'un programme
     * 
     * @param array $data Données à valider
     * @param int|null $excludeId ID à exclure pour validation unicité
     * @throws BusinessRuleException Si validation échoue
     */
    private function validateProgramData(array $data, ?int $excludeId = null): void
    {
        // Vérifier que le département existe et est actif
        $department = Department::find($data['department_id']);
        if (!$department || !$department->is_active) {
            throw new BusinessRuleException('Le département sélectionné n\'existe pas ou n\'est pas actif.');
        }
        
        // Vérifier l'unicité du code dans l'école
        $school = School::getActiveSchool();
        $codeQuery = Program::where('school_id', $school->id)
                           ->where('code', $data['code']);
                           
        if ($excludeId) {
            $codeQuery->where('id', '!=', $excludeId);
        }
        
        if ($codeQuery->exists()) {
            throw new BusinessRuleException('Ce code programme existe déjà dans votre établissement.');
        }
        
        // Valider la cohérence des crédits et durée
        if (isset($data['total_credits'], $data['duration_semesters'])) {
            $maxCreditsPerSemester = 60; // ECTS standard
            $minCreditsPerSemester = 15; // Minimum raisonnable
            
            $avgCreditsPerSemester = $data['total_credits'] / $data['duration_semesters'];
            
            if ($avgCreditsPerSemester > $maxCreditsPerSemester) {
                throw new BusinessRuleException(
                    "La charge de crédits par semestre ({$avgCreditsPerSemester}) dépasse le maximum recommandé ({$maxCreditsPerSemester} ECTS)."
                );
            }
            
            if ($avgCreditsPerSemester < $minCreditsPerSemester) {
                throw new BusinessRuleException(
                    "La charge de crédits par semestre ({$avgCreditsPerSemester}) est inférieure au minimum recommandé ({$minCreditsPerSemester} ECTS)."
                );
            }
        }
        
        // Valider la cohérence niveau/durée
        if (isset($data['level'], $data['duration_semesters'])) {
            $expectedDurations = [
                'licence' => [6, 8], // 3-4 ans
                'master' => [4, 6],  // 2-3 ans
                'doctorat' => [6, 8], // 3-4 ans
                'dut' => [4],        // 2 ans
                'bts' => [4],        // 2 ans
            ];
            
            $level = $data['level'];
            $duration = $data['duration_semesters'];
            
            if (isset($expectedDurations[$level]) && 
                !in_array($duration, $expectedDurations[$level])) {
                $expected = implode(' ou ', $expectedDurations[$level]);
                throw new BusinessRuleException(
                    "Pour un {$level}, la durée attendue est de {$expected} semestres, pas {$duration}."
                );
            }
        }
    }

    /**
     * Valider qu'un programme peut être supprimé
     * 
     * @param Program $program Programme à vérifier
     * @throws BusinessRuleException Si suppression non autorisée
     */
    private function validateProgramDeletion(Program $program): void
    {
        // Vérifier les semestres
        $semesterCount = $program->semesters()->count();
        if ($semesterCount > 0) {
            throw new BusinessRuleException(
                "Impossible de supprimer ce programme car il contient {$semesterCount} semestre(s)."
            );
        }
        
        // Note: Dans ce système, les enrollments sont liés aux classes, 
        // pas directement aux programmes. La validation des semestres
        // est suffisante car elle empêche la suppression si des UE existent.
    }

    /**
     * Obtenir les statistiques globales des programmes
     * 
     * @return array Statistiques détaillées
     */
    public function getGlobalProgramStats(): array
    {
        $school = School::getActiveSchool();
        
        $stats = [
            'total_programs' => Program::where('school_id', $school->id)->count(),
            'programs_by_level' => [],
            'total_semesters' => 0,
            'total_course_units' => 0,
            'total_credits' => 0,
            'avg_completion_rate' => 0,
        ];
        
        // Statistiques par niveau
        $programsByLevel = Program::where('school_id', $school->id)
            ->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->pluck('count', 'level')
            ->toArray();
            
        $stats['programs_by_level'] = $programsByLevel;
        
        // Statistiques détaillées
        $programs = Program::where('school_id', $school->id)
            ->with('semesters.courseUnits')
            ->get();
            
        $totalCompletionRate = 0;
        
        foreach ($programs as $program) {
            $stats['total_semesters'] += $program->semesters->count();
            $stats['total_course_units'] += $program->semesters->sum(function ($semester) {
                return $semester->courseUnits->count();
            });
            $stats['total_credits'] += $program->semesters->sum(function ($semester) {
                return $semester->courseUnits->sum('credits');
            });
            $totalCompletionRate += $this->calculateCompletionRate($program);
        }
        
        if ($stats['total_programs'] > 0) {
            $stats['avg_completion_rate'] = round($totalCompletionRate / $stats['total_programs'], 1);
        }
        
        return $stats;
    }

    /**
     * Préparer les données d'objectifs pour l'édition
     * 
     * @param Program $program Programme source
     * @return string Objectifs formatés en texte
     */
    public function formatObjectivesForEdit(Program $program): string
    {
        if (!$program->objectives || !is_array($program->objectives)) {
            return '';
        }
        
        return implode("\n", $program->objectives);
    }

    /**
     * Calculer les niveaux académiques disponibles pour un niveau de programme
     * 
     * @param string $level Niveau de programme
     * @return array Niveaux académiques avec descriptions
     */
    public function getAcademicLevelsForProgramLevel(string $level): array
    {
        switch ($level) {
            case 'licence':
                return [
                    1 => 'L1 - Première année de licence',
                    2 => 'L2 - Deuxième année de licence',
                    3 => 'L3 - Troisième année de licence',
                ];
                
            case 'master':
                return [
                    4 => 'M1 - Première année de master',
                    5 => 'M2 - Deuxième année de master',
                ];
                
            case 'doctorat':
                return [
                    6 => 'D1 - Première année de doctorat',
                    7 => 'D2 - Deuxième année de doctorat',
                    8 => 'D3 - Troisième année de doctorat',
                ];
                
            case 'dut':
            case 'bts':
                return [
                    1 => 'Première année',
                    2 => 'Deuxième année',
                ];
                
            default:
                return [];
        }
    }
}