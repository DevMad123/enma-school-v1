<?php

namespace App\Domains\Academic\University;

use App\Models\School;
use App\Models\CourseUnit;
use App\Models\Semester;
use App\Models\Program;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des unités d'enseignement universitaires
 * Centralise la logique métier spécifique aux UE du système LMD
 */
class CourseUnitManagementService
{
    /**
     * Créer une nouvelle unité d'enseignement
     *
     * @param array $data
     * @return CourseUnit
     * @throws \Exception
     */
    public function createCourseUnit(array $data): CourseUnit
    {
        return DB::transaction(function () use ($data) {
            // Valider les données
            $this->validateCourseUnitData($data);

            $courseUnit = CourseUnit::create([
                'school_id' => $data['school_id'],
                'semester_id' => $data['semester_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'short_name' => $data['short_name'] ?? null,
                'type' => $data['type'] ?? 'fundamental',
                'credits' => $data['credits'],
                'hours_cm' => $data['hours_cm'] ?? 0,
                'hours_td' => $data['hours_td'] ?? 0,
                'hours_tp' => $data['hours_tp'] ?? 0,
                'hours_total' => $this->calculateTotalHours($data),
                'hours_personal' => $data['hours_personal'] ?? 0,
                'description' => $data['description'] ?? null,
                'prerequisites' => $data['prerequisites'] ?? [],
                'learning_outcomes' => $data['learning_outcomes'] ?? [],
                'evaluation_method' => $data['evaluation_method'] ?? null,
                'coefficient' => $data['coefficient'] ?? 1.0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $courseUnit;
        });
    }

    /**
     * Calculer le total d'heures
     *
     * @param array $data
     * @return int
     */
    protected function calculateTotalHours(array $data): int
    {
        return ($data['hours_cm'] ?? 0) + ($data['hours_td'] ?? 0) + ($data['hours_tp'] ?? 0);
    }

    /**
     * Valider les données d'une UE
     *
     * @param array $data
     * @throws \Exception
     */
    protected function validateCourseUnitData(array $data): void
    {
        // Vérifier l'unicité du code dans le semestre
        $exists = CourseUnit::where('semester_id', $data['semester_id'])
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            throw new \Exception('Une UE avec ce code existe déjà dans ce semestre');
        }

        // Valider les crédits ECTS
        if ($data['credits'] < 1 || $data['credits'] > 30) {
            throw new \Exception('Le nombre de crédits doit être compris entre 1 et 30');
        }

        // Valider la cohérence heures/crédits
        $totalHours = $this->calculateTotalHours($data);
        $expectedHours = $data['credits'] * 25; // Standard: 25-30h par crédit
        $tolerance = $expectedHours * 0.3; // Tolérance de 30%

        if (abs($totalHours - $expectedHours) > $tolerance) {
            \Log::warning("Incohérence heures/crédits pour UE {$data['code']}: {$totalHours}h pour {$data['credits']} crédits");
        }
    }

    /**
     * Obtenir les UE d'un semestre
     *
     * @param Semester $semester
     * @param bool $activeOnly
     * @return Collection
     */
    public function getCourseUnitsBySemester(Semester $semester, bool $activeOnly = true): Collection
    {
        $query = CourseUnit::where('semester_id', $semester->id);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('type')->orderBy('name')->get();
    }

    /**
     * Obtenir les UE par type dans un programme
     *
     * @param Program $program
     * @param string $type
     * @return Collection
     */
    public function getCourseUnitsByType(Program $program, string $type): Collection
    {
        return CourseUnit::whereHas('semester', function ($query) use ($program) {
                $query->where('program_id', $program->id);
            })
            ->where('type', $type)
            ->where('is_active', true)
            ->with(['semester'])
            ->orderBy('semester_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Calculer les statistiques d'un semestre
     *
     * @param Semester $semester
     * @return array
     */
    public function getSemesterStatistics(Semester $semester): array
    {
        $courseUnits = $this->getCourseUnitsBySemester($semester);

        $totalCredits = $courseUnits->sum('credits');
        $totalHours = $courseUnits->sum('hours_total');

        $byType = $courseUnits->groupBy('type')->map(function ($units) {
            return [
                'count' => $units->count(),
                'credits' => $units->sum('credits'),
                'hours' => $units->sum('hours_total'),
            ];
        });

        return [
            'course_units_count' => $courseUnits->count(),
            'total_credits' => $totalCredits,
            'expected_credits' => $semester->credits_required,
            'credits_completion' => $semester->credits_required > 0 ? ($totalCredits / $semester->credits_required) * 100 : 0,
            'total_hours' => $totalHours,
            'average_hours_per_credit' => $totalCredits > 0 ? $totalHours / $totalCredits : 0,
            'by_type' => $byType,
        ];
    }

    /**
     * Valider la cohérence d'un semestre
     *
     * @param Semester $semester
     * @return array
     */
    public function validateSemesterCoherence(Semester $semester): array
    {
        $issues = [];
        $courseUnits = $this->getCourseUnitsBySemester($semester);
        $statistics = $this->getSemesterStatistics($semester);

        // Vérifier le nombre de crédits
        if ($statistics['total_credits'] !== $semester->credits_required) {
            $issues[] = "Total des crédits incorrect: {$statistics['total_credits']} au lieu de {$semester->credits_required}";
        }

        // Vérifier le nombre d'UE
        if ($courseUnits->count() < 4 || $courseUnits->count() > 10) {
            $issues[] = "Nombre d'UE non standard: {$courseUnits->count()} (recommandé: 4-10)";
        }

        // Vérifier l'équilibrage des types d'UE
        $fundamentalCredits = $courseUnits->where('type', 'fundamental')->sum('credits');
        $fundamentalPercentage = $statistics['total_credits'] > 0 ? ($fundamentalCredits / $statistics['total_credits']) * 100 : 0;

        if ($fundamentalPercentage < 60) {
            $issues[] = "Insuffisance d'UE fondamentales: {$fundamentalPercentage}% (minimum recommandé: 60%)";
        }

        // Vérifier les codes d'UE
        $duplicateCodes = $courseUnits->groupBy('code')->filter(function ($group) {
            return $group->count() > 1;
        });

        if ($duplicateCodes->count() > 0) {
            $issues[] = "Codes d'UE dupliqués: " . $duplicateCodes->keys()->implode(', ');
        }

        return [
            'is_coherent' => empty($issues),
            'issues' => $issues,
            'statistics' => $statistics,
        ];
    }

    /**
     * Synchroniser les crédits d'un semestre avec ses UE
     *
     * @param Semester $semester
     * @return Semester
     */
    public function synchronizeSemesterCredits(Semester $semester): Semester
    {
        $totalCredits = $this->getCourseUnitsBySemester($semester)->sum('credits');
        
        $semester->update(['credits_required' => $totalCredits]);
        
        return $semester;
    }

    /**
     * Dupliquer une UE vers un autre semestre
     *
     * @param CourseUnit $sourceCourseUnit
     * @param Semester $targetSemester
     * @param string|null $newCode
     * @return CourseUnit
     */
    public function duplicateCourseUnit(CourseUnit $sourceCourseUnit, Semester $targetSemester, ?string $newCode = null): CourseUnit
    {
        return DB::transaction(function () use ($sourceCourseUnit, $targetSemester, $newCode) {
            $code = $newCode ?? $sourceCourseUnit->code;

            // Vérifier que le code n'existe pas dans le semestre cible
            $exists = CourseUnit::where('semester_id', $targetSemester->id)
                ->where('code', $code)
                ->exists();

            if ($exists) {
                throw new \Exception("Une UE avec le code {$code} existe déjà dans le semestre cible");
            }

            $newCourseUnit = CourseUnit::create([
                'school_id' => $targetSemester->school_id,
                'semester_id' => $targetSemester->id,
                'name' => $sourceCourseUnit->name,
                'code' => $code,
                'short_name' => $sourceCourseUnit->short_name,
                'type' => $sourceCourseUnit->type,
                'credits' => $sourceCourseUnit->credits,
                'hours_cm' => $sourceCourseUnit->hours_cm,
                'hours_td' => $sourceCourseUnit->hours_td,
                'hours_tp' => $sourceCourseUnit->hours_tp,
                'hours_total' => $sourceCourseUnit->hours_total,
                'hours_personal' => $sourceCourseUnit->hours_personal,
                'description' => $sourceCourseUnit->description,
                'prerequisites' => $sourceCourseUnit->prerequisites,
                'learning_outcomes' => $sourceCourseUnit->learning_outcomes,
                'evaluation_method' => $sourceCourseUnit->evaluation_method,
                'coefficient' => $sourceCourseUnit->coefficient,
                'is_active' => true,
            ]);

            return $newCourseUnit;
        });
    }

    /**
     * Obtenir les UE avec des problèmes de cohérence
     *
     * @param School $school
     * @return array
     */
    public function getCourseUnitsWithIssues(School $school): array
    {
        $courseUnits = CourseUnit::where('school_id', $school->id)
            ->where('is_active', true)
            ->with(['semester.program'])
            ->get();

        $issues = [];

        foreach ($courseUnits as $courseUnit) {
            $courseUnitIssues = [];

            // Vérifier la cohérence heures/crédits
            $expectedHours = $courseUnit->credits * 25;
            $tolerance = $expectedHours * 0.3;
            if (abs($courseUnit->hours_total - $expectedHours) > $tolerance) {
                $courseUnitIssues[] = "Incohérence heures/crédits ({$courseUnit->hours_total}h pour {$courseUnit->credits} crédits)";
            }

            // Vérifier les prérequis
            if (!empty($courseUnit->prerequisites)) {
                foreach ($courseUnit->prerequisites as $prereq) {
                    $prereqExists = CourseUnit::where('code', $prereq)->exists();
                    if (!$prereqExists) {
                        $courseUnitIssues[] = "Prérequis inexistant: {$prereq}";
                    }
                }
            }

            // Vérifier la description
            if (empty($courseUnit->description) || strlen($courseUnit->description) < 50) {
                $courseUnitIssues[] = "Description insuffisante";
            }

            if (!empty($courseUnitIssues)) {
                $issues[] = [
                    'course_unit' => $courseUnit,
                    'issues' => $courseUnitIssues,
                ];
            }
        }

        return [
            'total_course_units' => $courseUnits->count(),
            'course_units_with_issues' => count($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Générer un rapport de charge horaire par programme
     *
     * @param Program $program
     * @return array
     */
    public function getProgramWorkloadReport(Program $program): array
    {
        $semesters = $program->semesters()->with(['courseUnits'])->get();
        
        $report = [
            'program' => $program,
            'total_course_units' => 0,
            'total_credits' => 0,
            'total_hours' => 0,
            'semesters' => [],
        ];

        foreach ($semesters as $semester) {
            $semesterStats = $this->getSemesterStatistics($semester);
            
            $report['total_course_units'] += $semesterStats['course_units_count'];
            $report['total_credits'] += $semesterStats['total_credits'];
            $report['total_hours'] += $semesterStats['total_hours'];
            
            $report['semesters'][] = [
                'semester' => $semester,
                'statistics' => $semesterStats,
            ];
        }

        $report['average_hours_per_semester'] = count($report['semesters']) > 0 ? $report['total_hours'] / count($report['semesters']) : 0;
        $report['average_course_units_per_semester'] = count($report['semesters']) > 0 ? $report['total_course_units'] / count($report['semesters']) : 0;

        return $report;
    }
}