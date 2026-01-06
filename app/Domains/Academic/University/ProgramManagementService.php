<?php

namespace App\Domains\Academic\University;

use App\Models\School;
use App\Models\Program;
use App\Models\Department;
use App\Models\Semester;
use App\Models\CourseUnit;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des programmes universitaires
 * Centralise la logique métier spécifique aux programmes LMD
 */
class ProgramManagementService
{
    /**
     * Créer un nouveau programme
     *
     * @param array $data
     * @return Program
     * @throws \Exception
     */
    public function createProgram(array $data): Program
    {
        return DB::transaction(function () use ($data) {
            // Valider les données
            $this->validateProgramData($data);

            $program = Program::create([
                'school_id' => $data['school_id'],
                'department_id' => $data['department_id'],
                'name' => $data['name'],
                'code' => $data['code'],
                'short_name' => $data['short_name'] ?? null,
                'level' => $data['level'], // licence, master, doctorat
                'duration_semesters' => $data['duration_semesters'],
                'total_credits' => $data['total_credits'],
                'description' => $data['description'] ?? null,
                'objectives' => $data['objectives'] ?? [],
                'diploma_title' => $data['diploma_title'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Générer automatiquement les semestres si demandé
            if ($data['auto_generate_semesters'] ?? false) {
                $this->generateSemesters($program);
            }

            return $program;
        });
    }

    /**
     * Valider les données d'un programme
     *
     * @param array $data
     * @throws \Exception
     */
    protected function validateProgramData(array $data): void
    {
        // Vérifier l'unicité du code dans l'école
        $exists = Program::where('school_id', $data['school_id'])
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            throw new \Exception('Un programme avec ce code existe déjà dans cet établissement');
        }

        // Valider la cohérence durée/crédits selon le niveau
        $this->validateLMDCompliance($data['level'], $data['duration_semesters'], $data['total_credits']);
    }

    /**
     * Valider la conformité LMD
     *
     * @param string $level
     * @param int $duration
     * @param int $credits
     * @throws \Exception
     */
    protected function validateLMDCompliance(string $level, int $duration, int $credits): void
    {
        $standards = [
            'licence' => ['duration' => 6, 'credits' => 180],
            'master' => ['duration' => 4, 'credits' => 120],
            'doctorat' => ['duration' => 6, 'credits' => 180],
        ];

        if (!isset($standards[$level])) {
            throw new \Exception('Niveau non conforme au système LMD');
        }

        $standard = $standards[$level];
        
        if ($duration !== $standard['duration']) {
            throw new \Exception("La durée pour un {$level} doit être de {$standard['duration']} semestres");
        }

        if ($credits !== $standard['credits']) {
            throw new \Exception("Le nombre de crédits pour un {$level} doit être de {$standard['credits']}");
        }
    }

    /**
     * Générer automatiquement les semestres d'un programme
     *
     * @param Program $program
     * @return Collection
     */
    public function generateSemesters(Program $program): Collection
    {
        $semesters = collect();
        $creditsPerSemester = $program->total_credits / $program->duration_semesters;

        for ($i = 1; $i <= $program->duration_semesters; $i++) {
            $semester = Semester::create([
                'school_id' => $program->school_id,
                'program_id' => $program->id,
                'name' => "Semestre {$i}",
                'code' => $program->code . "-S{$i}",
                'order' => $i,
                'credits_required' => $creditsPerSemester,
                'is_active' => true,
            ]);

            $semesters->push($semester);
        }

        return $semesters;
    }

    /**
     * Obtenir les programmes d'un département
     *
     * @param Department $department
     * @param string|null $level
     * @param bool $activeOnly
     * @return Collection
     */
    public function getProgramsByDepartment(Department $department, ?string $level = null, bool $activeOnly = true): Collection
    {
        $query = Program::where('department_id', $department->id);

        if ($level) {
            $query->where('level', $level);
        }

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->with(['semesters'])->orderBy('level')->orderBy('name')->get();
    }

    /**
     * Obtenir les programmes par niveau dans une école
     *
     * @param School $school
     * @param string $level
     * @return Collection
     */
    public function getProgramsByLevel(School $school, string $level): Collection
    {
        return Program::where('school_id', $school->id)
            ->where('level', $level)
            ->where('is_active', true)
            ->with(['department.ufr', 'semesters'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Calculer les statistiques d'un programme
     *
     * @param Program $program
     * @return array
     */
    public function getProgramStatistics(Program $program): array
    {
        $semesters = $program->semesters()->with(['courseUnits'])->get();
        
        $totalCourseUnits = $semesters->sum(function ($semester) {
            return $semester->courseUnits->count();
        });

        $totalDefinedCredits = $semesters->sum(function ($semester) {
            return $semester->courseUnits->sum('credits');
        });

        return [
            'semesters_count' => $semesters->count(),
            'course_units_count' => $totalCourseUnits,
            'total_defined_credits' => $totalDefinedCredits,
            'expected_credits' => $program->total_credits,
            'credits_completion' => $program->total_credits > 0 ? ($totalDefinedCredits / $program->total_credits) * 100 : 0,
            'average_course_units_per_semester' => $semesters->count() > 0 ? $totalCourseUnits / $semesters->count() : 0,
            // TODO: Ajouter les statistiques d'étudiants quand le module d'inscription sera implémenté
            'enrolled_students' => 0,
            'graduated_students' => 0,
        ];
    }

    /**
     * Valider la cohérence d'un programme
     *
     * @param Program $program
     * @return array
     */
    public function validateProgramCoherence(Program $program): array
    {
        $issues = [];
        $semesters = $program->semesters()->with(['courseUnits'])->get();

        // Vérifier le nombre de semestres
        if ($semesters->count() !== $program->duration_semesters) {
            $issues[] = "Nombre de semestres incorrect: {$semesters->count()} au lieu de {$program->duration_semesters}";
        }

        // Vérifier les crédits
        $totalCredits = $semesters->sum(function ($semester) {
            return $semester->courseUnits->sum('credits');
        });

        if ($totalCredits !== $program->total_credits) {
            $issues[] = "Total des crédits incorrect: {$totalCredits} au lieu de {$program->total_credits}";
        }

        // Vérifier l'équilibrage des semestres
        $expectedCreditsPerSemester = $program->total_credits / $program->duration_semesters;
        foreach ($semesters as $semester) {
            $semesterCredits = $semester->courseUnits->sum('credits');
            $deviation = abs($semesterCredits - $expectedCreditsPerSemester);
            
            if ($deviation > ($expectedCreditsPerSemester * 0.2)) { // Tolérance de 20%
                $issues[] = "Semestre {$semester->name} mal équilibré: {$semesterCredits} crédits (attendu ~{$expectedCreditsPerSemester})";
            }
        }

        return [
            'is_coherent' => empty($issues),
            'issues' => $issues,
            'statistics' => $this->getProgramStatistics($program),
        ];
    }

    /**
     * Dupliquer un programme vers un autre département
     *
     * @param Program $sourceProgram
     * @param Department $targetDepartment
     * @param string $newCode
     * @param string $newName
     * @return Program
     */
    public function duplicateProgram(Program $sourceProgram, Department $targetDepartment, string $newCode, string $newName): Program
    {
        return DB::transaction(function () use ($sourceProgram, $targetDepartment, $newCode, $newName) {
            // Créer le nouveau programme
            $newProgram = Program::create([
                'school_id' => $targetDepartment->school_id,
                'department_id' => $targetDepartment->id,
                'name' => $newName,
                'code' => $newCode,
                'short_name' => $sourceProgram->short_name,
                'level' => $sourceProgram->level,
                'duration_semesters' => $sourceProgram->duration_semesters,
                'total_credits' => $sourceProgram->total_credits,
                'description' => $sourceProgram->description,
                'objectives' => $sourceProgram->objectives,
                'diploma_title' => $sourceProgram->diploma_title,
                'is_active' => true,
            ]);

            // Dupliquer les semestres
            $sourceSemesters = $sourceProgram->semesters()->with(['courseUnits'])->get();
            foreach ($sourceSemesters as $sourceSemester) {
                $newSemester = Semester::create([
                    'school_id' => $newProgram->school_id,
                    'program_id' => $newProgram->id,
                    'name' => $sourceSemester->name,
                    'code' => $newProgram->code . "-S{$sourceSemester->order}",
                    'order' => $sourceSemester->order,
                    'credits_required' => $sourceSemester->credits_required,
                    'is_active' => true,
                ]);

                // Dupliquer les unités d'enseignement
                foreach ($sourceSemester->courseUnits as $sourceCourseUnit) {
                    CourseUnit::create([
                        'school_id' => $newProgram->school_id,
                        'semester_id' => $newSemester->id,
                        'name' => $sourceCourseUnit->name,
                        'code' => $sourceCourseUnit->code,
                        'short_name' => $sourceCourseUnit->short_name,
                        'type' => $sourceCourseUnit->type,
                        'credits' => $sourceCourseUnit->credits,
                        'hours_cm' => $sourceCourseUnit->hours_cm,
                        'hours_td' => $sourceCourseUnit->hours_td,
                        'hours_tp' => $sourceCourseUnit->hours_tp,
                        'hours_total' => $sourceCourseUnit->hours_total,
                        'description' => $sourceCourseUnit->description,
                        'prerequisites' => $sourceCourseUnit->prerequisites,
                        'learning_outcomes' => $sourceCourseUnit->learning_outcomes,
                        'evaluation_method' => $sourceCourseUnit->evaluation_method,
                        'coefficient' => $sourceCourseUnit->coefficient,
                        'is_active' => true,
                    ]);
                }
            }

            return $newProgram;
        });
    }

    /**
     * Obtenir les programmes nécessitant une attention
     *
     * @param School $school
     * @return array
     */
    public function getProgramsNeedingAttention(School $school): array
    {
        $programs = Program::where('school_id', $school->id)
            ->where('is_active', true)
            ->with(['semesters.courseUnits'])
            ->get();

        $issues = [];

        foreach ($programs as $program) {
            $validation = $this->validateProgramCoherence($program);
            
            if (!$validation['is_coherent']) {
                $issues[] = [
                    'program' => $program,
                    'issues' => $validation['issues'],
                    'statistics' => $validation['statistics'],
                ];
            }
        }

        return [
            'total_programs' => $programs->count(),
            'programs_with_issues' => count($issues),
            'issues' => $issues,
        ];
    }
}