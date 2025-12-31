<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;
use App\Models\AcademicTrack;
use App\Models\Subject;

class ModuleA3Seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Création de la structure académique MODULE A3...');

        // Récupérer ou créer l'école par défaut
        $school = School::first();
        if (!$school) {
            $school = School::create([
                'name' => 'École par défaut',
                'short_name' => 'EPD',
                'type' => 'secondary',
                'email' => 'contact@ecole.com',
                'phone' => '123456789',
                'address' => '123 Rue de l\'École',
                'city' => 'Yaoundé',
                'country' => 'Cameroun',
                'is_active' => true,
            ]);
        }

        // Récupérer l'année académique active
        $academicYear = AcademicYear::where('is_active', true)->first();
        if (!$academicYear) {
            $academicYear = AcademicYear::create([
                'school_id' => $school->id,
                'name' => '2024-2025',
                'start_date' => '2024-09-01',
                'end_date' => '2025-06-30',
                'is_active' => true,
                'is_current' => true,
            ]);
        }

        // Mise à jour des cycles existants pour l'école
        $cycles = Cycle::all();
        foreach ($cycles as $cycle) {
            $cycle->update([
                'school_id' => $school->id,
                'is_active' => true,
            ]);
        }

        // Mise à jour des niveaux existants pour l'école et année académique
        $levels = Level::all();
        foreach ($levels as $level) {
            $level->update([
                'school_id' => $school->id,
                'academic_year_id' => $academicYear->id,
                'type' => $this->getLevelType($level->name),
                'code' => $this->getLevelCode($level->name),
                'order' => $this->getLevelOrder($level->name),
                'is_active' => true,
            ]);
        }

        // Créer des filières pour le secondaire et universitaire
        $this->createAcademicTracks($school);

        // Créer des matières pour les différents niveaux
        $this->createSubjects($school);

        // Mettre à jour les classes existantes
        $this->updateExistingClasses($school, $academicYear);

        $this->command->info('✅ Structure académique MODULE A3 créée avec succès !');
    }

    private function createAcademicTracks(School $school)
    {
        $tracks = [
            ['name' => 'Scientifique', 'code' => 'S', 'description' => 'Filière scientifique'],
            ['name' => 'Littéraire', 'code' => 'L', 'description' => 'Filière littéraire'],
            ['name' => 'Informatique', 'code' => 'INF', 'description' => 'Licence en Informatique'],
        ];

        foreach ($tracks as $track) {
            AcademicTrack::firstOrCreate(
                ['school_id' => $school->id, 'code' => $track['code']],
                $track + ['is_active' => true]
            );
        }
    }

    private function createSubjects(School $school)
    {
        $subjects = [
            ['level_name' => '6e', 'name' => 'Français MODULE A3', 'code' => 'FR_A3', 'coefficient' => 3.0],
            ['level_name' => '6e', 'name' => 'Mathématiques MODULE A3', 'code' => 'MATH_A3', 'coefficient' => 3.0],
            ['level_name' => 'Tle', 'name' => 'Philosophie MODULE A3', 'code' => 'PHILO_A3', 'coefficient' => 3.0],
        ];

        foreach ($subjects as $subjectData) {
            $level = Level::where('school_id', $school->id)
                         ->where('name', $subjectData['level_name'])
                         ->first();

            if ($level) {
                Subject::updateOrCreate([
                    'school_id' => $school->id,
                    'level_id' => $level->id,
                    'code' => $subjectData['code']
                ], [
                    'name' => $subjectData['name'],
                    'coefficient' => $subjectData['coefficient'],
                    'volume_hour' => rand(20, 60),
                    'is_active' => true,
                ]);
            }
        }
    }

    private function updateExistingClasses(School $school, AcademicYear $academicYear)
    {
        SchoolClass::where('academic_year_id', $academicYear->id)
                  ->update(['is_active' => true]);
    }

    private function getLevelType(string $levelName): string
    {
        $secondaryLevels = ['6e', '5e', '4e', '3e', '2nd', '1ère', 'Tle'];
        return in_array($levelName, $secondaryLevels) ? 'secondary' : 'university';
    }

    private function getLevelCode(string $levelName): string
    {
        $codes = [
            '6e' => '6E', '5e' => '5E', '4e' => '4E', '3e' => '3E',
            '2nd' => '2ND', '1ère' => '1ERE', 'Tle' => 'TLE'
        ];
        return $codes[$levelName] ?? strtoupper($levelName);
    }

    private function getLevelOrder(string $levelName): int
    {
        $orders = [
            'CP1' => 1, 'CP2' => 2, 'CE1' => 3, 'CE2' => 4, 'CM1' => 5, 'CM2' => 6,
            '6e' => 7, '5e' => 8, '4e' => 9, '3e' => 10, '2nd' => 11, '1ère' => 12, 'Tle' => 13
        ];
        return $orders[$levelName] ?? 99;
    }
}
