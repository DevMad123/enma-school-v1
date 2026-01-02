<?php

namespace App\Services;

use App\Models\Semester;
use App\Models\Program;
use App\Models\AcademicYear;
use App\Models\School;
use App\Exceptions\BusinessRuleException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Service pour la gestion des semestres
 * 
 * Ce service centralise la logique métier pour :
 * - Gestion des semestres et calculs de dates
 * - Validation des règles métier pour semestres
 * - Calculs de niveaux académiques
 * - Opérations CRUD avec gestion des dépendances
 * 
 * @package App\Services
 * @author N'golo Madou OUATTARA
 * @version 1.0
 * @since 2026-01-02
 */
class SemesterService
{
    /**
     * Créer un nouveau semestre avec validation complète
     * 
     * @param Program $program Programme parent
     * @param array $data Données du semestre
     * @return Semester Semestre créé
     * @throws BusinessRuleException Si validation échoue
     */
    public function createSemester(Program $program, array $data): Semester
    {
        try {
            DB::beginTransaction();
            
            $school = School::getActiveSchool();
            $currentAcademicYear = AcademicYear::currentForSchool($school->id)->first();
            
            // Validation de l'année académique
            if (!$currentAcademicYear) {
                throw new BusinessRuleException(
                    'Aucune année académique courante définie pour cette école. Veuillez d\'abord définir une année académique comme courante.'
                );
            }
            
            // Vérifier l'unicité du numéro de semestre
            $this->validateSemesterUniqueness($program, $currentAcademicYear, $data['semester_number']);
            
            // Préparer les données complètes
            $semesterData = $this->prepareSemesterData($program, $currentAcademicYear, $data);
            
            // Créer le semestre
            $semester = Semester::create($semesterData);
            
            // Logger la création
            Log::info('Semestre créé', [
                'semester_id' => $semester->id,
                'semester_name' => $semester->name,
                'program_id' => $program->id,
                'academic_year_id' => $currentAcademicYear->id,
                'created_by' => auth()->id()
            ]);
            
            DB::commit();
            return $semester;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur création semestre', [
                'program_id' => $program->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Mettre à jour un semestre avec validation
     * 
     * @param Semester $semester Semestre à mettre à jour
     * @param array $data Nouvelles données
     * @return Semester Semestre mis à jour
     * @throws BusinessRuleException Si validation échoue
     */
    public function updateSemester(Semester $semester, array $data): Semester
    {
        try {
            DB::beginTransaction();
            
            // Validation de l'unicité (en excluant le semestre actuel)
            $this->validateSemesterUniqueness(
                $semester->program, 
                $semester->academicYear, 
                $data['semester_number'], 
                $semester->id
            );
            
            // Préparer les données mises à jour
            $updateData = $this->prepareUpdateData($semester, $data);
            
            // Mettre à jour
            $semester->update($updateData);
            $semester->refresh();
            
            // Logger la mise à jour
            Log::info('Semestre mis à jour', [
                'semester_id' => $semester->id,
                'semester_name' => $semester->name,
                'updated_by' => auth()->id()
            ]);
            
            DB::commit();
            return $semester;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise à jour semestre', [
                'semester_id' => $semester->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Supprimer un semestre avec vérifications
     * 
     * @param Semester $semester Semestre à supprimer
     * @throws BusinessRuleException Si suppression non autorisée
     */
    public function deleteSemester(Semester $semester): void
    {
        try {
            DB::beginTransaction();
            
            // Vérifier les dépendances
            $this->validateSemesterDeletion($semester);
            
            $semesterId = $semester->id;
            $semesterName = $semester->name;
            
            // Supprimer le semestre
            $semester->delete();
            
            // Logger la suppression
            Log::info('Semestre supprimé', [
                'semester_id' => $semesterId,
                'semester_name' => $semesterName,
                'deleted_by' => auth()->id()
            ]);
            
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur suppression semestre', [
                'semester_id' => $semester->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculer les statistiques d'un semestre
     * 
     * @param Semester $semester Semestre à analyser
     * @return array Statistiques détaillées
     */
    public function calculateSemesterStats(Semester $semester): array
    {
        $semester->loadMissing('courseUnits');
        
        $courseUnits = $semester->courseUnits;
        
        return [
            'total_course_units' => $courseUnits->count(),
            'total_credits' => $courseUnits->sum('credits'),
            'total_hours' => $courseUnits->sum('hours_total'),
            'credits_completion' => $this->calculateCreditsCompletion($semester),
            'hours_distribution' => $this->calculateHoursDistribution($courseUnits),
            'course_unit_types' => $this->calculateCourseUnitTypes($courseUnits),
        ];
    }

    /**
     * Obtenir le niveau académique selon le niveau et le numéro de semestre
     * 
     * @param string $level Niveau de programme
     * @param int $semesterNumber Numéro du semestre
     * @return int Niveau académique (1-8)
     */
    public function getAcademicLevel(string $level, int $semesterNumber): int
    {
        switch ($level) {
            case 'licence':
                // L1: 1-2, L2: 3-4, L3: 5-6
                return ceil($semesterNumber / 2);
                
            case 'master':
                // M1: 1-2 (niveau 4-5), M2: 3-4 (niveau 5-6)
                return 3 + ceil($semesterNumber / 2);
                
            case 'doctorat':
                // D1: 1-2 (niveau 6-7), D2: 3-4 (niveau 7-8), etc.
                return 5 + ceil($semesterNumber / 2);
                
            case 'dut':
            case 'bts':
                // Niveau post-bac : 1-2
                return ceil($semesterNumber / 2);
                
            default:
                return $semesterNumber;
        }
    }

    /**
     * Calculer les dates de début et fin d'un semestre
     * 
     * @param AcademicYear $academicYear Année académique
     * @param int $semesterNumber Numéro du semestre
     * @param int $totalSemesters Nombre total de semestres du programme
     * @return array Dates de début et fin
     */
    public function getSemesterDates(AcademicYear $academicYear, int $semesterNumber, int $totalSemesters): array
    {
        $startDate = \Carbon\Carbon::parse($academicYear->start_date);
        $endDate = \Carbon\Carbon::parse($academicYear->end_date);
        
        // Calculer la durée totale de l'année académique en jours
        $totalDays = $startDate->diffInDays($endDate);
        
        // Pour les programmes multi-années, on divise l'année académique en semestres
        // En supposant 2 semestres par année académique
        $semestersPerYear = min($totalSemesters, 2);
        $semesterDurationDays = intval($totalDays / $semestersPerYear);
        
        // Calculer les dates pour ce semestre
        // Les semestres impairs commencent plus tôt, les pairs plus tard dans l'année
        $semesterIndex = (($semesterNumber - 1) % $semestersPerYear);
        
        $semesterStartDate = $startDate->copy()->addDays($semesterIndex * $semesterDurationDays);
        
        // La date de fin est soit le début du semestre suivant - 1 jour, soit la fin de l'année académique
        if ($semesterIndex < $semestersPerYear - 1) {
            $semesterEndDate = $semesterStartDate->copy()->addDays($semesterDurationDays - 1);
        } else {
            $semesterEndDate = $endDate->copy();
        }
        
        return [
            'start_date' => $semesterStartDate,
            'end_date' => $semesterEndDate,
        ];
    }

    /**
     * Valider l'unicité d'un semestre
     */
    private function validateSemesterUniqueness(
        Program $program, 
        AcademicYear $academicYear, 
        int $semesterNumber, 
        ?int $excludeId = null
    ): void {
        $query = Semester::where('program_id', $program->id)
                          ->where('academic_year_id', $academicYear->id)
                          ->where('semester_number', $semesterNumber);
                          
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        if ($query->exists()) {
            throw new BusinessRuleException(
                'Un semestre avec ce numéro existe déjà pour ce programme dans l\'année académique courante.'
            );
        }
    }

    /**
     * Préparer les données complètes pour la création d'un semestre
     */
    private function prepareSemesterData(Program $program, AcademicYear $academicYear, array $data): array
    {
        $school = School::getActiveSchool();
        
        $semesterData = $data;
        $semesterData['program_id'] = $program->id;
        $semesterData['school_id'] = $school->id;
        $semesterData['academic_year_id'] = $academicYear->id;
        $semesterData['is_active'] = $data['status'] === 'active';
        $semesterData['is_current'] = false; // Par défaut, pas le semestre courant
        
        // Calculer le niveau académique
        $semesterData['academic_level'] = $this->getAcademicLevel(
            $program->level, 
            $data['semester_number']
        );
        
        // Calculer les dates du semestre
        $dates = $this->getSemesterDates(
            $academicYear, 
            $data['semester_number'], 
            $program->duration_semesters
        );
        $semesterData['start_date'] = $dates['start_date'];
        $semesterData['end_date'] = $dates['end_date'];
        
        unset($semesterData['status']);
        
        return $semesterData;
    }

    /**
     * Préparer les données pour la mise à jour d'un semestre
     */
    private function prepareUpdateData(Semester $semester, array $data): array
    {
        $updateData = $data;
        
        // Recalculer le niveau académique et les dates si le numéro de semestre a changé
        if ($data['semester_number'] != $semester->semester_number) {
            $updateData['academic_level'] = $this->getAcademicLevel(
                $semester->program->level, 
                $data['semester_number']
            );
            
            $dates = $this->getSemesterDates(
                $semester->academicYear, 
                $data['semester_number'], 
                $semester->program->duration_semesters
            );
            $updateData['start_date'] = $dates['start_date'];
            $updateData['end_date'] = $dates['end_date'];
        }
        
        $updateData['is_active'] = $data['status'] === 'active';
        unset($updateData['status']);
        
        return $updateData;
    }

    /**
     * Valider qu'un semestre peut être supprimé
     */
    private function validateSemesterDeletion(Semester $semester): void
    {
        $courseUnitCount = $semester->courseUnits()->count();
        
        if ($courseUnitCount > 0) {
            throw new BusinessRuleException(
                "Impossible de supprimer ce semestre car il contient {$courseUnitCount} unité(s) d'enseignement."
            );
        }
        
        // Vérifier les inscriptions si le modèle existe
        if (class_exists(\App\Models\Enrollment::class)) {
            $enrollmentCount = \App\Models\Enrollment::where('semester_id', $semester->id)->count();
            if ($enrollmentCount > 0) {
                throw new BusinessRuleException(
                    "Impossible de supprimer ce semestre car {$enrollmentCount} étudiant(s) y sont inscrits."
                );
            }
        }
    }

    /**
     * Calculer le taux de complétion des crédits
     */
    private function calculateCreditsCompletion(Semester $semester): array
    {
        $requiredCredits = $semester->required_credits;
        $actualCredits = $semester->courseUnits->sum('credits');
        
        $completionRate = $requiredCredits > 0 ? ($actualCredits / $requiredCredits) * 100 : 0;
        
        return [
            'required' => $requiredCredits,
            'actual' => $actualCredits,
            'rate' => round($completionRate, 1),
            'remaining' => max(0, $requiredCredits - $actualCredits),
        ];
    }

    /**
     * Calculer la distribution des heures
     */
    private function calculateHoursDistribution($courseUnits): array
    {
        return [
            'total_hours' => $courseUnits->sum('hours_total'),
            'cm_hours' => $courseUnits->sum('hours_cm'),
            'td_hours' => $courseUnits->sum('hours_td'),
            'tp_hours' => $courseUnits->sum('hours_tp'),
        ];
    }

    /**
     * Calculer la répartition par type d'UE
     */
    private function calculateCourseUnitTypes($courseUnits): array
    {
        return $courseUnits->groupBy('type')
                          ->map(function ($units) {
                              return $units->count();
                          })
                          ->toArray();
    }
}