<?php

namespace App\Domains\Academic\PreUniversity;

use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Level;
use App\Models\Cycle;
use App\Models\Student;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des classes pour le préuniversitaire
 * Centralise la logique métier spécifique aux classes scolaires
 */
class ClassManagementService
{
    /**
     * Créer une nouvelle classe
     *
     * @param array $data
     * @return SchoolClass
     * @throws \Exception
     */
    public function createClass(array $data): SchoolClass
    {
        return DB::transaction(function () use ($data) {
            // Valider la cohérence des données
            $this->validateClassData($data);

            $class = SchoolClass::create([
                'school_id' => $data['school_id'],
                'academic_year_id' => $data['academic_year_id'],
                'level_id' => $data['level_id'],
                'cycle_id' => $data['cycle_id'],
                'academic_track_id' => $data['academic_track_id'] ?? null,
                'name' => $data['name'],
                'capacity' => $data['capacity'] ?? 30,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $class;
        });
    }

    /**
     * Valider les données d'une classe
     *
     * @param array $data
     * @throws \Exception
     */
    protected function validateClassData(array $data): void
    {
        // Vérifier que le niveau appartient au bon cycle
        $level = Level::find($data['level_id']);
        if (!$level || $level->cycle_id !== $data['cycle_id']) {
            throw new \Exception('Le niveau ne correspond pas au cycle sélectionné');
        }

        // Vérifier l'unicité du nom dans le niveau pour cette année
        $exists = SchoolClass::where('academic_year_id', $data['academic_year_id'])
            ->where('level_id', $data['level_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            throw new \Exception('Une classe avec ce nom existe déjà pour ce niveau cette année');
        }
    }

    /**
     * Affecter un élève à une classe
     *
     * @param Student $student
     * @param SchoolClass $class
     * @return void
     * @throws \Exception
     */
    public function enrollStudent(Student $student, SchoolClass $class): void
    {
        DB::transaction(function () use ($student, $class) {
            // Vérifier la capacité
            if ($this->isClassFull($class)) {
                throw new \Exception('La classe a atteint sa capacité maximale');
            }

            // Vérifier si l'élève n'est pas déjà dans une autre classe cette année
            $existingClass = $this->getStudentCurrentClass($student, $class->academic_year_id);
            if ($existingClass && $existingClass->id !== $class->id) {
                throw new \Exception('L\'élève est déjà affecté à une autre classe cette année');
            }

            // Affecter l'élève
            $student->classes()->syncWithoutDetaching([
                $class->id => ['assigned_at' => now()]
            ]);

            // Créer l'inscription si elle n'existe pas
            if (!$student->enrollments()
                    ->where('academic_year_id', $class->academic_year_id)
                    ->where('class_id', $class->id)
                    ->exists()) {
                
                $student->enrollments()->create([
                    'academic_year_id' => $class->academic_year_id,
                    'class_id' => $class->id,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);
            }
        });
    }

    /**
     * Désaffecter un élève d'une classe
     *
     * @param Student $student
     * @param SchoolClass $class
     * @return void
     */
    public function unenrollStudent(Student $student, SchoolClass $class): void
    {
        DB::transaction(function () use ($student, $class) {
            // Retirer de la table pivot
            $student->classes()->detach($class->id);

            // Mettre à jour l'inscription
            $student->enrollments()
                ->where('academic_year_id', $class->academic_year_id)
                ->where('class_id', $class->id)
                ->update(['status' => 'cancelled']);
        });
    }

    /**
     * Vérifier si une classe est pleine
     *
     * @param SchoolClass $class
     * @return bool
     */
    public function isClassFull(SchoolClass $class): bool
    {
        return $class->students()->count() >= $class->capacity;
    }

    /**
     * Obtenir la classe actuelle d'un élève
     *
     * @param Student $student
     * @param int $academicYearId
     * @return SchoolClass|null
     */
    public function getStudentCurrentClass(Student $student, int $academicYearId): ?SchoolClass
    {
        return $student->classes()
            ->where('academic_year_id', $academicYearId)
            ->first();
    }

    /**
     * Obtenir les classes d'un niveau
     *
     * @param Level $level
     * @param AcademicYear|null $academicYear
     * @return Collection
     */
    public function getClassesByLevel(Level $level, ?AcademicYear $academicYear = null): Collection
    {
        $query = SchoolClass::where('level_id', $level->id);

        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }

        return $query->with(['students', 'academicYear'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir les classes d'un cycle
     *
     * @param Cycle $cycle
     * @param AcademicYear|null $academicYear
     * @return Collection
     */
    public function getClassesByCycle(Cycle $cycle, ?AcademicYear $academicYear = null): Collection
    {
        $query = SchoolClass::where('cycle_id', $cycle->id);

        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }

        return $query->with(['level', 'students', 'academicYear'])
            ->orderBy('level_id')
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtenir les statistiques d'une classe
     *
     * @param SchoolClass $class
     * @return array
     */
    public function getClassStatistics(SchoolClass $class): array
    {
        return [
            'total_students' => $class->students()->count(),
            'capacity' => $class->capacity,
            'available_spots' => $class->capacity - $class->students()->count(),
            'fill_rate' => ($class->students()->count() / $class->capacity) * 100,
            'boys_count' => $class->students()->where('gender', 'male')->count(),
            'girls_count' => $class->students()->where('gender', 'female')->count(),
            'active_enrollments' => $class->enrollments()->where('status', 'active')->count(),
        ];
    }

    /**
     * Dupliquer une classe pour une nouvelle année
     *
     * @param SchoolClass $sourceClass
     * @param AcademicYear $targetYear
     * @param bool $copyStudents
     * @return SchoolClass
     */
    public function duplicateClass(SchoolClass $sourceClass, AcademicYear $targetYear, bool $copyStudents = false): SchoolClass
    {
        return DB::transaction(function () use ($sourceClass, $targetYear, $copyStudents) {
            $newClass = SchoolClass::create([
                'school_id' => $sourceClass->school_id,
                'academic_year_id' => $targetYear->id,
                'level_id' => $sourceClass->level_id,
                'cycle_id' => $sourceClass->cycle_id,
                'academic_track_id' => $sourceClass->academic_track_id,
                'name' => $sourceClass->name,
                'capacity' => $sourceClass->capacity,
                'is_active' => true,
            ]);

            if ($copyStudents) {
                $students = $sourceClass->students()->get();
                foreach ($students as $student) {
                    try {
                        $this->enrollStudent($student, $newClass);
                    } catch (\Exception $e) {
                        // Log l'erreur mais continue avec les autres étudiants
                        \Log::warning("Impossible d'inscrire l'élève {$student->id} dans la nouvelle classe: " . $e->getMessage());
                    }
                }
            }

            return $newClass;
        });
    }

    /**
     * Obtenir le taux de remplissage des classes d'un établissement
     *
     * @param School $school
     * @param AcademicYear|null $academicYear
     * @return array
     */
    public function getSchoolClassesFillRate(School $school, ?AcademicYear $academicYear = null): array
    {
        $query = SchoolClass::where('school_id', $school->id);

        if ($academicYear) {
            $query->where('academic_year_id', $academicYear->id);
        }

        $classes = $query->withCount('students')->get();

        $totalCapacity = $classes->sum('capacity');
        $totalStudents = $classes->sum('students_count');

        return [
            'total_classes' => $classes->count(),
            'total_capacity' => $totalCapacity,
            'total_students' => $totalStudents,
            'global_fill_rate' => $totalCapacity > 0 ? ($totalStudents / $totalCapacity) * 100 : 0,
            'classes_details' => $classes->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'capacity' => $class->capacity,
                    'students_count' => $class->students_count,
                    'fill_rate' => $class->capacity > 0 ? ($class->students_count / $class->capacity) * 100 : 0,
                ];
            }),
        ];
    }
}