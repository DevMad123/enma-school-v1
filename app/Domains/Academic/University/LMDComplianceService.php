<?php

namespace App\Domains\Academic\University;

use App\Models\School;
use App\Models\Program;
use App\Models\Semester;
use App\Models\CourseUnit;

/**
 * Service de conformité LMD
 * Valide et assure la conformité aux standards du système LMD
 */
class LMDComplianceService
{
    /**
     * Standards LMD officiels
     */
    protected const LMD_STANDARDS = [
        'licence' => [
            'duration_semesters' => 6,
            'total_credits' => 180,
            'credits_per_semester' => 30,
            'min_course_units_per_semester' => 4,
            'max_course_units_per_semester' => 8,
        ],
        'master' => [
            'duration_semesters' => 4,
            'total_credits' => 120,
            'credits_per_semester' => 30,
            'min_course_units_per_semester' => 4,
            'max_course_units_per_semester' => 6,
        ],
        'doctorat' => [
            'duration_semesters' => 6,
            'total_credits' => 180,
            'credits_per_semester' => 30,
            'min_course_units_per_semester' => 3,
            'max_course_units_per_semester' => 5,
        ],
    ];

    /**
     * Valider la conformité LMD d'un programme
     *
     * @param Program $program
     * @return array
     */
    public function validateProgram(Program $program): array
    {
        $standard = $this->getLMDStandard($program->level);
        $issues = [];
        $recommendations = [];

        // Validation de base
        $baseValidation = $this->validateBasicStructure($program, $standard);
        $issues = array_merge($issues, $baseValidation['issues']);
        $recommendations = array_merge($recommendations, $baseValidation['recommendations']);

        // Validation des semestres
        $semesterValidation = $this->validateSemesters($program, $standard);
        $issues = array_merge($issues, $semesterValidation['issues']);
        $recommendations = array_merge($recommendations, $semesterValidation['recommendations']);

        // Validation des unités d'enseignement
        $courseUnitValidation = $this->validateCourseUnits($program, $standard);
        $issues = array_merge($issues, $courseUnitValidation['issues']);
        $recommendations = array_merge($recommendations, $courseUnitValidation['recommendations']);

        // Calcul du score de conformité
        $conformityScore = $this->calculateConformityScore($program, $standard);

        return [
            'is_compliant' => empty($issues),
            'conformity_score' => $conformityScore,
            'issues' => $issues,
            'recommendations' => $recommendations,
            'standard' => $standard,
        ];
    }

    /**
     * Obtenir le standard LMD pour un niveau
     *
     * @param string $level
     * @return array
     * @throws \Exception
     */
    protected function getLMDStandard(string $level): array
    {
        if (!isset(self::LMD_STANDARDS[$level])) {
            throw new \Exception("Niveau LMD non reconnu: {$level}");
        }

        return self::LMD_STANDARDS[$level];
    }

    /**
     * Valider la structure de base d'un programme
     *
     * @param Program $program
     * @param array $standard
     * @return array
     */
    protected function validateBasicStructure(Program $program, array $standard): array
    {
        $issues = [];
        $recommendations = [];

        // Vérifier la durée
        if ($program->duration_semesters !== $standard['duration_semesters']) {
            $issues[] = "Durée incorrecte: {$program->duration_semesters} semestres au lieu de {$standard['duration_semesters']}";
        }

        // Vérifier les crédits totaux
        if ($program->total_credits !== $standard['total_credits']) {
            $issues[] = "Nombre de crédits incorrect: {$program->total_credits} au lieu de {$standard['total_credits']}";
        }

        // Vérifier les objectifs pédagogiques
        if (empty($program->objectives) || count($program->objectives) < 3) {
            $recommendations[] = "Définir au moins 3 objectifs pédagogiques clairs";
        }

        // Vérifier la description
        if (empty($program->description) || strlen($program->description) < 200) {
            $recommendations[] = "Enrichir la description du programme (minimum 200 caractères)";
        }

        return [
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Valider les semestres d'un programme
     *
     * @param Program $program
     * @param array $standard
     * @return array
     */
    protected function validateSemesters(Program $program, array $standard): array
    {
        $issues = [];
        $recommendations = [];
        $semesters = $program->semesters()->with(['courseUnits'])->get();

        // Vérifier le nombre de semestres
        if ($semesters->count() !== $standard['duration_semesters']) {
            $issues[] = "Nombre de semestres incorrect: {$semesters->count()} au lieu de {$standard['duration_semesters']}";
        }

        // Valider chaque semestre
        foreach ($semesters as $semester) {
            $semesterIssues = $this->validateSemester($semester, $standard);
            $issues = array_merge($issues, $semesterIssues['issues']);
            $recommendations = array_merge($recommendations, $semesterIssues['recommendations']);
        }

        return [
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Valider un semestre individual
     *
     * @param Semester $semester
     * @param array $standard
     * @return array
     */
    protected function validateSemester(Semester $semester, array $standard): array
    {
        $issues = [];
        $recommendations = [];
        $courseUnits = $semester->courseUnits;

        // Vérifier les crédits du semestre
        $totalCredits = $courseUnits->sum('credits');
        if ($totalCredits !== $standard['credits_per_semester']) {
            $issues[] = "Semestre {$semester->name}: {$totalCredits} crédits au lieu de {$standard['credits_per_semester']}";
        }

        // Vérifier le nombre d'UE
        $courseUnitsCount = $courseUnits->count();
        if ($courseUnitsCount < $standard['min_course_units_per_semester'] || 
            $courseUnitsCount > $standard['max_course_units_per_semester']) {
            $recommendations[] = "Semestre {$semester->name}: {$courseUnitsCount} UE (recommandé: {$standard['min_course_units_per_semester']}-{$standard['max_course_units_per_semester']})";
        }

        // Vérifier l'équilibrage des types d'UE
        $typeBalance = $this->analyzeUETypeBalance($courseUnits);
        if ($typeBalance['fundamental_percentage'] < 60) {
            $recommendations[] = "Semestre {$semester->name}: Insuffisance d'UE fondamentales ({$typeBalance['fundamental_percentage']}%)";
        }

        return [
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Valider les unités d'enseignement d'un programme
     *
     * @param Program $program
     * @param array $standard
     * @return array
     */
    protected function validateCourseUnits(Program $program, array $standard): array
    {
        $issues = [];
        $recommendations = [];

        $allCourseUnits = CourseUnit::whereHas('semester', function ($query) use ($program) {
            $query->where('program_id', $program->id);
        })->get();

        // Vérifier les codes uniques
        $duplicateCodes = $allCourseUnits->groupBy('code')
            ->filter(function ($group) {
                return $group->count() > 1;
            });

        if ($duplicateCodes->count() > 0) {
            $issues[] = "Codes d'UE dupliqués: " . $duplicateCodes->keys()->implode(', ');
        }

        // Vérifier la cohérence heures/crédits
        $incoherentUnits = $allCourseUnits->filter(function ($unit) {
            $expectedHours = $unit->credits * 25; // Standard: 25h par crédit
            $tolerance = $expectedHours * 0.3;
            return abs($unit->hours_total - $expectedHours) > $tolerance;
        });

        if ($incoherentUnits->count() > 0) {
            $recommendations[] = "Vérifier la cohérence heures/crédits pour " . $incoherentUnits->count() . " UE";
        }

        // Vérifier les prérequis
        $this->validatePrerequisites($allCourseUnits, $issues, $recommendations);

        return [
            'issues' => $issues,
            'recommendations' => $recommendations,
        ];
    }

    /**
     * Analyser l'équilibrage des types d'UE
     *
     * @param \Illuminate\Support\Collection $courseUnits
     * @return array
     */
    protected function analyzeUETypeBalance($courseUnits): array
    {
        $totalCredits = $courseUnits->sum('credits');
        $byType = $courseUnits->groupBy('type')->map(function ($units) {
            return $units->sum('credits');
        });

        return [
            'total_credits' => $totalCredits,
            'by_type' => $byType,
            'fundamental_percentage' => $totalCredits > 0 ? (($byType['fundamental'] ?? 0) / $totalCredits) * 100 : 0,
            'complementary_percentage' => $totalCredits > 0 ? (($byType['complementary'] ?? 0) / $totalCredits) * 100 : 0,
            'optional_percentage' => $totalCredits > 0 ? (($byType['optional'] ?? 0) / $totalCredits) * 100 : 0,
        ];
    }

    /**
     * Valider les prérequis des UE
     *
     * @param \Illuminate\Support\Collection $courseUnits
     * @param array &$issues
     * @param array &$recommendations
     * @return void
     */
    protected function validatePrerequisites($courseUnits, array &$issues, array &$recommendations): void
    {
        $allCodes = $courseUnits->pluck('code')->toArray();

        foreach ($courseUnits as $unit) {
            if (!empty($unit->prerequisites)) {
                foreach ($unit->prerequisites as $prereq) {
                    if (!in_array($prereq, $allCodes)) {
                        $issues[] = "UE {$unit->code}: prérequis inexistant '{$prereq}'";
                    }
                }
            }
        }

        // Recommander l'ajout de prérequis si nécessaire
        $advancedUnits = $courseUnits->filter(function ($unit) {
            return str_contains(strtolower($unit->name), 'avancé') || 
                   str_contains(strtolower($unit->name), 'approfondi') ||
                   preg_match('/\b(II|2|avancé)\b/i', $unit->name);
        });

        $unitsWithoutPrereq = $advancedUnits->filter(function ($unit) {
            return empty($unit->prerequisites);
        });

        if ($unitsWithoutPrereq->count() > 0) {
            $recommendations[] = "Considérer l'ajout de prérequis pour " . $unitsWithoutPrereq->count() . " UE avancées";
        }
    }

    /**
     * Calculer le score de conformité LMD
     *
     * @param Program $program
     * @param array $standard
     * @return float
     */
    protected function calculateConformityScore(Program $program, array $standard): float
    {
        $score = 100.0;
        $semesters = $program->semesters()->with(['courseUnits'])->get();

        // Pénalités pour structure de base
        if ($program->duration_semesters !== $standard['duration_semesters']) {
            $score -= 20;
        }
        if ($program->total_credits !== $standard['total_credits']) {
            $score -= 20;
        }

        // Pénalités pour semestres
        $expectedSemesters = $standard['duration_semesters'];
        if ($semesters->count() !== $expectedSemesters) {
            $score -= 15;
        }

        // Pénalités pour équilibrage des crédits
        foreach ($semesters as $semester) {
            $semesterCredits = $semester->courseUnits->sum('credits');
            $deviation = abs($semesterCredits - $standard['credits_per_semester']);
            if ($deviation > 3) { // Tolérance de ±3 crédits
                $score -= 5 * ($deviation - 3);
            }
        }

        // Pénalités pour nombre d'UE
        foreach ($semesters as $semester) {
            $unitsCount = $semester->courseUnits->count();
            if ($unitsCount < $standard['min_course_units_per_semester'] || 
                $unitsCount > $standard['max_course_units_per_semester']) {
                $score -= 3;
            }
        }

        return max(0, $score);
    }

    /**
     * Générer un rapport de conformité pour une école
     *
     * @param School $school
     * @return array
     */
    public function generateSchoolComplianceReport(School $school): array
    {
        $programs = Program::where('school_id', $school->id)
            ->where('is_active', true)
            ->with(['semesters.courseUnits'])
            ->get();

        $report = [
            'school' => $school,
            'total_programs' => $programs->count(),
            'compliant_programs' => 0,
            'average_score' => 0,
            'programs' => [],
        ];

        $totalScore = 0;

        foreach ($programs as $program) {
            $validation = $this->validateProgram($program);
            
            if ($validation['is_compliant']) {
                $report['compliant_programs']++;
            }

            $totalScore += $validation['conformity_score'];

            $report['programs'][] = [
                'program' => $program,
                'validation' => $validation,
            ];
        }

        $report['average_score'] = $programs->count() > 0 ? $totalScore / $programs->count() : 0;
        $report['compliance_rate'] = $programs->count() > 0 ? ($report['compliant_programs'] / $programs->count()) * 100 : 0;

        return $report;
    }

    /**
     * Obtenir les recommandations LMD pour un niveau
     *
     * @param string $level
     * @return array
     */
    public function getLMDRecommendations(string $level): array
    {
        $standard = $this->getLMDStandard($level);

        return [
            'structure' => [
                'duration' => "{$standard['duration_semesters']} semestres",
                'total_credits' => "{$standard['total_credits']} crédits ECTS",
                'credits_per_semester' => "{$standard['credits_per_semester']} crédits par semestre",
            ],
            'course_units' => [
                'per_semester' => "{$standard['min_course_units_per_semester']}-{$standard['max_course_units_per_semester']} UE par semestre",
                'fundamental' => "60% minimum d'UE fondamentales",
                'complementary' => "30% maximum d'UE complémentaires",
                'optional' => "10% maximum d'UE optionnelles",
            ],
            'credits' => [
                'distribution' => "3-6 crédits par UE recommandés",
                'workload' => "25-30 heures de travail par crédit",
                'evaluation' => "Note minimale 10/20 pour validation",
            ],
        ];
    }
}