<?php

namespace App\Domains\Academic;

use App\Models\School;
use App\Models\AcademicYear;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service de gestion des années académiques
 * Centralise la logique métier des périodes académiques
 */
class AcademicYearService
{
    /**
     * Créer une nouvelle année académique avec ses périodes
     *
     * @param School $school
     * @param array $data
     * @return AcademicYear
     */
    public function createAcademicYear(School $school, array $data): AcademicYear
    {
        return DB::transaction(function () use ($school, $data) {
            // Désactiver l'année précédente si nécessaire
            if ($data['is_active'] ?? false) {
                $this->deactivateOtherYears($school);
            }

            $academicYear = AcademicYear::create([
                'school_id' => $school->id,
                'name' => $data['name'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_active' => $data['is_active'] ?? false,
                'status' => $data['status'] ?? 'planned',
            ]);

            // Générer les périodes automatiquement
            if ($data['auto_generate_periods'] ?? false) {
                $this->generatePeriods($academicYear, $data['period_type'] ?? 'trimestre');
            }

            return $academicYear;
        });
    }

    /**
     * Générer automatiquement les périodes d'une année académique
     *
     * @param AcademicYear $academicYear
     * @param string $periodType
     * @return Collection
     */
    public function generatePeriods(AcademicYear $academicYear, string $periodType = 'trimestre'): Collection
    {
        $periods = collect();
        $startDate = Carbon::parse($academicYear->start_date);
        $endDate = Carbon::parse($academicYear->end_date);

        switch ($periodType) {
            case 'trimestre':
                $periods = $this->generateTrimesters($academicYear, $startDate, $endDate);
                break;
            case 'semestre':
                $periods = $this->generateSemesters($academicYear, $startDate, $endDate);
                break;
        }

        return $periods;
    }

    /**
     * Générer les trimestres
     *
     * @param AcademicYear $academicYear
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    protected function generateTrimesters(AcademicYear $academicYear, Carbon $startDate, Carbon $endDate): Collection
    {
        $periods = collect();
        $totalDays = $startDate->diffInDays($endDate);
        $periodDays = intval($totalDays / 3);

        for ($i = 1; $i <= 3; $i++) {
            $periodStart = $i === 1 ? $startDate->copy() : $startDate->copy()->addDays(($i - 1) * $periodDays);
            $periodEnd = $i === 3 ? $endDate->copy() : $periodStart->copy()->addDays($periodDays - 1);

            $period = $academicYear->periods()->create([
                'name' => "Trimestre {$i}",
                'type' => 'trimestre',
                'order' => $i,
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'is_active' => $i === 1, // Premier trimestre actif par défaut
            ]);

            $periods->push($period);
        }

        return $periods;
    }

    /**
     * Générer les semestres
     *
     * @param AcademicYear $academicYear
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return Collection
     */
    protected function generateSemesters(AcademicYear $academicYear, Carbon $startDate, Carbon $endDate): Collection
    {
        $periods = collect();
        $totalDays = $startDate->diffInDays($endDate);
        $semesterDays = intval($totalDays / 2);

        for ($i = 1; $i <= 2; $i++) {
            $periodStart = $i === 1 ? $startDate->copy() : $startDate->copy()->addDays($semesterDays);
            $periodEnd = $i === 2 ? $endDate->copy() : $periodStart->copy()->addDays($semesterDays - 1);

            $period = $academicYear->periods()->create([
                'name' => "Semestre {$i}",
                'type' => 'semestre',
                'order' => $i,
                'start_date' => $periodStart,
                'end_date' => $periodEnd,
                'is_active' => $i === 1, // Premier semestre actif par défaut
            ]);

            $periods->push($period);
        }

        return $periods;
    }

    /**
     * Désactiver toutes les autres années académiques
     *
     * @param School $school
     * @return void
     */
    protected function deactivateOtherYears(School $school): void
    {
        AcademicYear::where('school_id', $school->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }

    /**
     * Activer une année académique
     *
     * @param AcademicYear $academicYear
     * @return AcademicYear
     */
    public function activateYear(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear) {
            // Désactiver les autres années
            $this->deactivateOtherYears($academicYear->school);

            // Activer cette année
            $academicYear->update(['is_active' => true, 'status' => 'active']);

            return $academicYear;
        });
    }

    /**
     * Clôturer une année académique
     *
     * @param AcademicYear $academicYear
     * @return AcademicYear
     */
    public function closeYear(AcademicYear $academicYear): AcademicYear
    {
        return DB::transaction(function () use ($academicYear) {
            $academicYear->update([
                'status' => 'closed',
                'is_active' => false,
                'closed_at' => now(),
            ]);

            // Clôturer toutes les périodes
            $academicYear->periods()->update([
                'is_active' => false,
                'status' => 'closed',
            ]);

            return $academicYear;
        });
    }

    /**
     * Obtenir l'année académique courante
     *
     * @param School $school
     * @return AcademicYear|null
     */
    public function getCurrentYear(School $school): ?AcademicYear
    {
        return AcademicYear::where('school_id', $school->id)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Obtenir la période courante
     *
     * @param AcademicYear $academicYear
     * @return \App\Models\AcademicPeriod|null
     */
    public function getCurrentPeriod(AcademicYear $academicYear)
    {
        return $academicYear->periods()
            ->where('is_active', true)
            ->first();
    }

    /**
     * Changer la période active
     *
     * @param AcademicYear $academicYear
     * @param int $periodId
     * @return void
     */
    public function setActivePeriod(AcademicYear $academicYear, int $periodId): void
    {
        DB::transaction(function () use ($academicYear, $periodId) {
            // Désactiver toutes les périodes
            $academicYear->periods()->update(['is_active' => false]);

            // Activer la période demandée
            $academicYear->periods()
                ->where('id', $periodId)
                ->update(['is_active' => true]);
        });
    }

    /**
     * Obtenir les statistiques d'une année académique
     *
     * @param AcademicYear $academicYear
     * @return array
     */
    public function getYearStatistics(AcademicYear $academicYear): array
    {
        $school = $academicYear->school;
        $stats = [
            'periods_count' => $academicYear->periods()->count(),
            'active_periods_count' => $academicYear->periods()->where('is_active', true)->count(),
        ];

        if ($school->isPreUniversity()) {
            $stats = array_merge($stats, [
                'enrollments_count' => $academicYear->enrollments()->count(),
                'active_enrollments_count' => $academicYear->enrollments()->where('status', 'active')->count(),
                'classes_count' => $academicYear->classes()->count(),
            ]);
        }

        if ($school->isUniversity()) {
            $stats = array_merge($stats, [
                'university_enrollments_count' => $academicYear->universityEnrollments()->count() ?? 0,
                'active_programs_count' => $academicYear->programs()->where('is_active', true)->count() ?? 0,
            ]);
        }

        return $stats;
    }
}