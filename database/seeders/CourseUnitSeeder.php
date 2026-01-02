<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseUnit;
use App\Models\School;
use App\Models\Semester;
use App\Models\AcademicYear;
use Faker\Factory as Faker;

class CourseUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        
        // Récupérer les écoles et semestres
        $school = School::first();
        if (!$school) {
            $this->command->error('Aucune école trouvée. Veuillez d\'abord exécuter SchoolSeeder.');
            return;
        }

        $semesters = Semester::where('school_id', $school->id)->get();
        if ($semesters->isEmpty()) {
            $this->command->error('Aucun semestre trouvé pour cette école.');
            return;
        }

        $this->command->info('Création des UE pour tests ECUE...');

        // Données d'exemple pour différents types d'UE
        $ueData = [
            // UE de base
            [
                'code' => 'INF101',
                'name' => 'Introduction à l\'informatique',
                'type' => 'obligatoire',
                'credits' => 4,
                'hours_cm' => 20,
                'hours_td' => 15,
                'hours_tp' => 10,
            ],
            [
                'code' => 'MAT201',
                'name' => 'Mathématiques pour l\'informatique',
                'type' => 'obligatoire',
                'credits' => 6,
                'hours_cm' => 30,
                'hours_td' => 20,
                'hours_tp' => 0,
            ],
            [
                'code' => 'PRG301',
                'name' => 'Programmation orientée objet',
                'type' => 'obligatoire',
                'credits' => 5,
                'hours_cm' => 15,
                'hours_td' => 15,
                'hours_tp' => 20,
            ],
            [
                'code' => 'WEB401',
                'name' => 'Développement Web avancé',
                'type' => 'optionnel',
                'credits' => 3,
                'hours_cm' => 10,
                'hours_td' => 10,
                'hours_tp' => 15,
            ],
            [
                'code' => 'BDD501',
                'name' => 'Bases de données relationnelles',
                'type' => 'obligatoire',
                'credits' => 4,
                'hours_cm' => 18,
                'hours_td' => 12,
                'hours_tp' => 15,
            ]
        ];

        foreach ($ueData as $index => $data) {
            $semester = $semesters[$index % $semesters->count()];
            
            $courseUnit = CourseUnit::create([
                'school_id' => $school->id,
                'semester_id' => $semester->id,
                'code' => $data['code'],
                'name' => $data['name'],
                'short_name' => substr($data['name'], 0, 20),
                'type' => $data['type'],
                'credits' => $data['credits'],
                'hours_cm' => $data['hours_cm'],
                'hours_td' => $data['hours_td'],
                'hours_tp' => $data['hours_tp'],
                'hours_total' => $data['hours_cm'] + $data['hours_td'] + $data['hours_tp'],
                'hours_personal' => $data['credits'] * 25 - ($data['hours_cm'] + $data['hours_td'] + $data['hours_tp']),
                'description' => "UE {$data['name']} - {$data['credits']} crédits ECTS",
                'evaluation_method' => $faker->randomElement(['controle_continu', 'examen_final', 'mixte']),
                'coefficient' => 1.0,
                'is_active' => true,
                // Nouveaux champs pour synchronisation ECUE
                'sync_credits_with_elements' => true,
                'sync_hours_with_elements' => true,
                'auto_sync_enabled' => true,
                'elements_count' => 0,
            ]);

            $this->command->line("✓ {$courseUnit->code} - {$courseUnit->name} ({$courseUnit->credits} crédits)");
        }

        $this->command->info('UE créées avec succès !');
    }
}
