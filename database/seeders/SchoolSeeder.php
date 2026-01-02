<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des écoles de test...');
        
        // Vérifier s'il y a déjà des écoles
        if (School::count() > 0) {
            $this->command->warn('Des écoles existent déjà dans la base de données.');
            $this->command->info('Écoles existantes :');
            School::all()->each(function($school) {
                $this->command->line("  - {$school->name} ({$school->academic_system})");
            });
            return;
        }
        
        // École principale - Système trimestre
        $school1 = School::create([
            'name' => 'École Enma School',
            'short_name' => 'EES',
            'type' => 'pre_university',
            'educational_levels' => ['primary', 'secondary'],
            'email' => 'contact@enmaschool.edu.ci',
            'phone' => '+225 01 02 03 04 05',
            'address' => '123 Rue de l\'Éducation, Cocody',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'academic_system' => 'trimestre',
            'grading_system' => '20',
            'is_active' => true,
        ]);

        // École secondaire - Système semestre
        $school2 = School::create([
            'name' => 'Collège Moderne d\'Abidjan',
            'short_name' => 'CMA',
            'type' => 'pre_university',
            'educational_levels' => ['secondary'],
            'email' => 'info@collegemoderne.edu.ci',
            'phone' => '+225 01 02 03 04 06',
            'address' => '456 Boulevard de la Paix, Plateau',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'academic_system' => 'semestre',
            'grading_system' => '20',
            'is_active' => true,
        ]);
        
        // École primaire - Système trimestre
        $school3 = School::create([
            'name' => 'Groupe Scolaire Les Palmiers',
            'short_name' => 'GSP',
            'type' => 'pre_university',
            'educational_levels' => ['primary', 'secondary'],
            'email' => 'direction@lespalmiers.edu.ci',
            'phone' => '+225 01 02 03 04 07',
            'address' => '789 Rue des Palmiers, Marcory',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'academic_system' => 'trimestre',
            'grading_system' => '20',
            'is_active' => true,
        ]);

        // Ajouter les paramètres pour chaque école
        foreach ([$school1, $school2, $school3] as $index => $school) {
            $mottos = [
                'Excellence et Innovation',
                'Savoir, Discipline et Réussite',
                'Grandir dans la Joie d\'Apprendre'
            ];
            
            $school->setSetting('school_motto', $mottos[$index]);
            $school->setSetting('academic_year_start', '2025-09-01');
            $school->setSetting('academic_year_end', '2026-06-30');
            $school->setSetting('max_students_per_class', '35');
            $school->setSetting('passing_grade', '10');
            $school->setSetting('excellence_grade', '16');
            
            $this->command->line("École créée : {$school->name} (Système : {$school->academic_system})");
        }
        
        $this->command->info('Écoles créées avec succès !');
    }
}
