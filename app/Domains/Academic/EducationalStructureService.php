<?php

namespace App\Domains\Academic;

use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;

/**
 * Service transversal pour la gestion des structures éducatives
 * Gère les éléments communs entre préuniversitaire et universitaire
 */
class EducationalStructureService
{
    /**
     * Obtenir la structure éducative complète d'un établissement
     *
     * @param School $school
     * @return array
     */
    public function getCompleteStructure(School $school): array
    {
        $structure = [
            'school' => $school,
            'type' => $school->type,
            'academic_years' => $this->getAcademicYears($school),
        ];

        if ($school->isPreUniversity()) {
            $structure['preuniversity'] = $this->getPreUniversityStructure($school);
        }

        if ($school->isUniversity()) {
            $structure['university'] = $this->getUniversityStructure($school);
        }

        return $structure;
    }

    /**
     * Obtenir les années académiques d'un établissement
     *
     * @param School $school
     * @return Collection
     */
    public function getAcademicYears(School $school): Collection
    {
        return AcademicYear::where('school_id', $school->id)
            ->with(['periods'])
            ->orderBy('start_date', 'desc')
            ->get();
    }

    /**
     * Obtenir la structure préuniversitaire
     *
     * @param School $school
     * @return array
     */
    protected function getPreUniversityStructure(School $school): array
    {
        return [
            'cycles' => $school->cycles()->with(['levels.classes'])->get(),
            'total_levels' => $school->levels()->count(),
            'total_classes' => $school->classes()->count(),
            'total_students' => $school->students()->count(),
        ];
    }

    /**
     * Obtenir la structure universitaire
     *
     * @param School $school
     * @return array
     */
    protected function getUniversityStructure(School $school): array
    {
        return [
            'ufrs' => $school->ufrs()->with(['departments.programs.semesters'])->get(),
            'total_departments' => $school->departments()->count(),
            'total_programs' => $school->programs()->count(),
            'total_course_units' => $school->courseUnits()->count(),
        ];
    }

    /**
     * Valider la cohérence d'une structure éducative
     *
     * @param School $school
     * @return array
     */
    public function validateStructure(School $school): array
    {
        $issues = [];

        // Vérifications communes
        if (!$school->academicYears()->where('is_active', true)->exists()) {
            $issues[] = 'Aucune année académique active trouvée';
        }

        // Vérifications spécialisées
        if ($school->isPreUniversity()) {
            $issues = array_merge($issues, $this->validatePreUniversityStructure($school));
        }

        if ($school->isUniversity()) {
            $issues = array_merge($issues, $this->validateUniversityStructure($school));
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
        ];
    }

    /**
     * Valider la structure préuniversitaire
     *
     * @param School $school
     * @return array
     */
    protected function validatePreUniversityStructure(School $school): array
    {
        $issues = [];

        if (!$school->cycles()->exists()) {
            $issues[] = 'Aucun cycle configuré';
        }

        if (!$school->levels()->exists()) {
            $issues[] = 'Aucun niveau configuré';
        }

        return $issues;
    }

    /**
     * Valider la structure universitaire
     *
     * @param School $school
     * @return array
     */
    protected function validateUniversityStructure(School $school): array
    {
        $issues = [];

        if (!$school->ufrs()->exists()) {
            $issues[] = 'Aucune UFR configurée';
        }

        if (!$school->departments()->exists()) {
            $issues[] = 'Aucun département configuré';
        }

        return $issues;
    }

    /**
     * Obtenir les statistiques globales d'un établissement
     *
     * @param School $school
     * @return array
     */
    public function getGlobalStatistics(School $school): array
    {
        $stats = [
            'students_count' => $school->students()->count(),
            'teachers_count' => $school->teachers()->count(),
            'staff_count' => $school->staff()->count(),
        ];

        if ($school->isPreUniversity()) {
            $stats = array_merge($stats, [
                'cycles_count' => $school->cycles()->count(),
                'levels_count' => $school->levels()->count(),
                'classes_count' => $school->classes()->count(),
                'subjects_count' => $school->subjects()->count(),
            ]);
        }

        if ($school->isUniversity()) {
            $stats = array_merge($stats, [
                'ufrs_count' => $school->ufrs()->count(),
                'departments_count' => $school->departments()->count(),
                'programs_count' => $school->programs()->count(),
                'course_units_count' => $school->courseUnits()->count(),
            ]);
        }

        return $stats;
    }
}