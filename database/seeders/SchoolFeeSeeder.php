<?php

namespace Database\Seeders;

use App\Models\SchoolFee;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Level;
use App\Models\Cycle;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $academicYear = AcademicYear::first();
        $schoolClasses = SchoolClass::all();
        $levels = Level::all();
        $cycles = Cycle::all();

        if (!$academicYear) {
            $this->command->warn('Aucune année académique trouvée. Assurez-vous que les seeders précédents ont été exécutés.');
            return;
        }

        // Frais généraux (tous les étudiants)
        SchoolFee::create([
            'name' => 'Frais d\'inscription',
            'description' => 'Frais d\'inscription obligatoires pour tous les nouveaux étudiants',
            'amount' => 50000,
            'academic_year_id' => $academicYear->id,
            'is_mandatory' => true,
            'due_date' => now()->addDays(30),
            'status' => 'active'
        ]);

        SchoolFee::create([
            'name' => 'Frais de scolarité',
            'description' => 'Frais de scolarité annuels pour tous les étudiants',
            'amount' => 200000,
            'academic_year_id' => $academicYear->id,
            'is_mandatory' => true,
            'due_date' => now()->addDays(60),
            'status' => 'active'
        ]);

        SchoolFee::create([
            'name' => 'Frais de fournitures scolaires',
            'description' => 'Achat de fournitures scolaires pour l\'année',
            'amount' => 75000,
            'academic_year_id' => $academicYear->id,
            'is_mandatory' => true,
            'due_date' => now()->addDays(45),
            'status' => 'active'
        ]);

        // Frais par cycle si disponibles
        if ($cycles->count() > 0) {
            foreach ($cycles as $cycle) {
                SchoolFee::create([
                    'name' => "Frais spéciaux - {$cycle->name}",
                    'description' => "Frais spécifiques au cycle {$cycle->name}",
                    'amount' => rand(25000, 100000),
                    'cycle_id' => $cycle->id,
                    'academic_year_id' => $academicYear->id,
                    'is_mandatory' => rand(0, 1),
                    'due_date' => now()->addDays(rand(30, 90)),
                    'status' => 'active'
                ]);
            }
        }

        // Frais par niveau si disponibles
        if ($levels->count() > 0) {
            foreach ($levels->take(3) as $level) {
                SchoolFee::create([
                    'name' => "Frais d\'examen - {$level->name}",
                    'description' => "Frais d\'examen pour le niveau {$level->name}",
                    'amount' => rand(15000, 50000),
                    'level_id' => $level->id,
                    'academic_year_id' => $academicYear->id,
                    'is_mandatory' => true,
                    'due_date' => now()->addDays(rand(60, 120)),
                    'status' => 'active'
                ]);
            }
        }

        // Frais par classe spécifique si disponibles
        if ($schoolClasses->count() > 0) {
            foreach ($schoolClasses->take(2) as $class) {
                SchoolFee::create([
                    'name' => "Sortie éducative - {$class->name}",
                    'description' => "Frais pour la sortie éducative de la classe {$class->name}",
                    'amount' => rand(10000, 30000),
                    'school_class_id' => $class->id,
                    'academic_year_id' => $academicYear->id,
                    'is_mandatory' => false,
                    'due_date' => now()->addDays(rand(90, 150)),
                    'status' => 'active'
                ]);
            }
        }

        // Frais optionnels
        SchoolFee::create([
            'name' => 'Frais de transport',
            'description' => 'Service de transport scolaire (optionnel)',
            'amount' => 40000,
            'academic_year_id' => $academicYear->id,
            'is_mandatory' => false,
            'due_date' => null,
            'status' => 'active'
        ]);

        SchoolFee::create([
            'name' => 'Frais de cantine',
            'description' => 'Service de restauration scolaire (optionnel)',
            'amount' => 60000,
            'academic_year_id' => $academicYear->id,
            'is_mandatory' => false,
            'due_date' => null,
            'status' => 'active'
        ]);

        $this->command->info('Frais scolaires créés avec succès.');
    }
}
