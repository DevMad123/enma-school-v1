<?php

namespace App\Domains\Academic\PreUniversity;

use App\Models\School;
use App\Models\Subject;
use App\Models\Level;
use App\Models\AcademicTrack;
use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service de gestion des matières pour le préuniversitaire
 * Centralise la logique métier spécifique aux matières scolaires
 */
class SubjectManagementService
{
    /**
     * Créer une nouvelle matière
     *
     * @param array $data
     * @return Subject
     * @throws \Exception
     */
    public function createSubject(array $data): Subject
    {
        return DB::transaction(function () use ($data) {
            // Valider les données
            $this->validateSubjectData($data);

            $subject = Subject::create([
                'school_id' => $data['school_id'],
                'level_id' => $data['level_id'],
                'academic_track_id' => $data['academic_track_id'] ?? null,
                'name' => $data['name'],
                'code' => $data['code'],
                'coefficient' => $data['coefficient'] ?? 1.0,
                'volume_hour' => $data['volume_hour'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $subject;
        });
    }

    /**
     * Valider les données d'une matière
     *
     * @param array $data
     * @throws \Exception
     */
    protected function validateSubjectData(array $data): void
    {
        // Vérifier l'unicité du code dans le niveau
        $exists = Subject::where('school_id', $data['school_id'])
            ->where('level_id', $data['level_id'])
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            throw new \Exception('Une matière avec ce code existe déjà pour ce niveau');
        }

        // Valider le coefficient
        if (isset($data['coefficient']) && ($data['coefficient'] <= 0 || $data['coefficient'] > 10)) {
            throw new \Exception('Le coefficient doit être compris entre 0.1 et 10');
        }
    }

    /**
     * Obtenir les matières d'un niveau
     *
     * @param Level $level
     * @param bool $activeOnly
     * @return Collection
     */
    public function getSubjectsByLevel(Level $level, bool $activeOnly = true): Collection
    {
        $query = Subject::where('level_id', $level->id);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Obtenir les matières d'une filière
     *
     * @param AcademicTrack $track
     * @param bool $activeOnly
     * @return Collection
     */
    public function getSubjectsByTrack(AcademicTrack $track, bool $activeOnly = true): Collection
    {
        $query = Subject::where('academic_track_id', $track->id);

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->with(['level'])->orderBy('name')->get();
    }

    /**
     * Affecter un enseignant à une matière
     *
     * @param Teacher $teacher
     * @param Subject $subject
     * @param array $classes
     * @param array $options
     * @return void
     */
    public function assignTeacherToSubject(Teacher $teacher, Subject $subject, array $classes = [], array $options = []): void
    {
        DB::transaction(function () use ($teacher, $subject, $classes, $options) {
            foreach ($classes as $classId) {
                $assignment = $teacher->teacherAssignments()->updateOrCreate(
                    [
                        'subject_id' => $subject->id,
                        'class_id' => $classId,
                    ],
                    [
                        'academic_year_id' => $options['academic_year_id'] ?? null,
                        'assignment_type' => $options['assignment_type'] ?? 'regular',
                        'weekly_hours' => $options['weekly_hours'] ?? null,
                        'start_date' => $options['start_date'] ?? now(),
                        'end_date' => $options['end_date'] ?? null,
                        'is_active' => true,
                    ]
                );
            }
        });
    }

    /**
     * Obtenir la charge horaire d'un niveau
     *
     * @param Level $level
     * @return array
     */
    public function getLevelWorkload(Level $level): array
    {
        $subjects = $this->getSubjectsByLevel($level);

        $totalHours = $subjects->sum('volume_hour');
        $totalCoefficient = $subjects->sum('coefficient');

        return [
            'subjects_count' => $subjects->count(),
            'total_hours' => $totalHours,
            'total_coefficient' => $totalCoefficient,
            'average_hours_per_subject' => $subjects->count() > 0 ? $totalHours / $subjects->count() : 0,
            'subjects' => $subjects->map(function ($subject) {
                return [
                    'id' => $subject->id,
                    'name' => $subject->name,
                    'code' => $subject->code,
                    'coefficient' => $subject->coefficient,
                    'volume_hour' => $subject->volume_hour,
                    'teachers_count' => $subject->teachers()->count(),
                ];
            }),
        ];
    }

    /**
     * Dupliquer les matières d'un niveau vers un autre
     *
     * @param Level $sourceLevel
     * @param Level $targetLevel
     * @param bool $copyAssignments
     * @return Collection
     */
    public function duplicateSubjectsToLevel(Level $sourceLevel, Level $targetLevel, bool $copyAssignments = false): Collection
    {
        return DB::transaction(function () use ($sourceLevel, $targetLevel, $copyAssignments) {
            $sourceSubjects = $this->getSubjectsByLevel($sourceLevel, false);
            $duplicatedSubjects = collect();

            foreach ($sourceSubjects as $sourceSubject) {
                // Vérifier si la matière n'existe pas déjà
                $exists = Subject::where('level_id', $targetLevel->id)
                    ->where('code', $sourceSubject->code)
                    ->exists();

                if (!$exists) {
                    $newSubject = Subject::create([
                        'school_id' => $targetLevel->school_id,
                        'level_id' => $targetLevel->id,
                        'academic_track_id' => $sourceSubject->academic_track_id,
                        'name' => $sourceSubject->name,
                        'code' => $sourceSubject->code,
                        'coefficient' => $sourceSubject->coefficient,
                        'volume_hour' => $sourceSubject->volume_hour,
                        'is_active' => $sourceSubject->is_active,
                    ]);

                    $duplicatedSubjects->push($newSubject);

                    // Copier les affectations si demandé
                    if ($copyAssignments) {
                        $this->copySubjectAssignments($sourceSubject, $newSubject);
                    }
                }
            }

            return $duplicatedSubjects;
        });
    }

    /**
     * Copier les affectations d'enseignants d'une matière à une autre
     *
     * @param Subject $sourceSubject
     * @param Subject $targetSubject
     * @return void
     */
    protected function copySubjectAssignments(Subject $sourceSubject, Subject $targetSubject): void
    {
        $assignments = $sourceSubject->teacherAssignments()
            ->with(['teacher', 'schoolClass'])
            ->get();

        foreach ($assignments as $assignment) {
            // Trouver la classe équivalente dans le niveau cible
            $targetClass = \App\Models\SchoolClass::where('level_id', $targetSubject->level_id)
                ->where('name', $assignment->schoolClass->name)
                ->first();

            if ($targetClass && $assignment->teacher) {
                $this->assignTeacherToSubject(
                    $assignment->teacher,
                    $targetSubject,
                    [$targetClass->id],
                    [
                        'academic_year_id' => $assignment->academic_year_id,
                        'assignment_type' => $assignment->assignment_type,
                        'weekly_hours' => $assignment->weekly_hours,
                    ]
                );
            }
        }
    }

    /**
     * Calculer la charge d'enseignement d'un enseignant
     *
     * @param Teacher $teacher
     * @param int|null $academicYearId
     * @return array
     */
    public function getTeacherWorkload(Teacher $teacher, ?int $academicYearId = null): array
    {
        $query = $teacher->teacherAssignments()->with(['subject', 'schoolClass']);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        $assignments = $query->get();

        $totalHours = $assignments->sum('weekly_hours');
        $subjectsCount = $assignments->pluck('subject_id')->unique()->count();
        $classesCount = $assignments->pluck('class_id')->unique()->count();

        return [
            'assignments_count' => $assignments->count(),
            'subjects_count' => $subjectsCount,
            'classes_count' => $classesCount,
            'total_weekly_hours' => $totalHours,
            'assignments' => $assignments->map(function ($assignment) {
                return [
                    'subject' => $assignment->subject->name,
                    'class' => $assignment->schoolClass->name,
                    'weekly_hours' => $assignment->weekly_hours,
                    'assignment_type' => $assignment->assignment_type,
                ];
            }),
        ];
    }

    /**
     * Obtenir les matières sans enseignant affecté
     *
     * @param School $school
     * @param int|null $academicYearId
     * @return Collection
     */
    public function getUnassignedSubjects(School $school, ?int $academicYearId = null): Collection
    {
        $query = Subject::where('school_id', $school->id)
            ->where('is_active', true)
            ->whereDoesntHave('teacherAssignments', function ($q) use ($academicYearId) {
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
            });

        return $query->with(['level'])->get();
    }

    /**
     * Générer un rapport de répartition des matières
     *
     * @param School $school
     * @param int|null $academicYearId
     * @return array
     */
    public function getSubjectDistributionReport(School $school, ?int $academicYearId = null): array
    {
        $subjects = Subject::where('school_id', $school->id)
            ->where('is_active', true)
            ->with(['level', 'teacherAssignments' => function ($q) use ($academicYearId) {
                if ($academicYearId) {
                    $q->where('academic_year_id', $academicYearId);
                }
            }])
            ->get();

        $assignedSubjects = $subjects->filter(function ($subject) {
            return $subject->teacherAssignments->count() > 0;
        });

        $unassignedSubjects = $subjects->filter(function ($subject) {
            return $subject->teacherAssignments->count() === 0;
        });

        return [
            'total_subjects' => $subjects->count(),
            'assigned_subjects' => $assignedSubjects->count(),
            'unassigned_subjects' => $unassignedSubjects->count(),
            'assignment_rate' => $subjects->count() > 0 ? ($assignedSubjects->count() / $subjects->count()) * 100 : 0,
            'by_level' => $subjects->groupBy('level.name')->map(function ($levelSubjects) {
                $assigned = $levelSubjects->filter(function ($subject) {
                    return $subject->teacherAssignments->count() > 0;
                });

                return [
                    'total' => $levelSubjects->count(),
                    'assigned' => $assigned->count(),
                    'unassigned' => $levelSubjects->count() - $assigned->count(),
                    'rate' => $levelSubjects->count() > 0 ? ($assigned->count() / $levelSubjects->count()) * 100 : 0,
                ];
            }),
        ];
    }
}