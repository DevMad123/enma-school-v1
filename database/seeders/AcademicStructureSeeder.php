<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\SchoolClass;

class AcademicStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer les années académiques
        $currentYear = AcademicYear::firstOrCreate([
            'name' => '2024-2025',
        ], [
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_active' => true,
        ]);

        $nextYear = AcademicYear::firstOrCreate([
            'name' => '2025-2026',
        ], [
            'start_date' => '2025-09-01',
            'end_date' => '2026-06-30',
            'is_active' => false,
        ]);

        // Créer les cycles
        $primaire = Cycle::firstOrCreate(['name' => 'Primaire']);
        $secondaire = Cycle::firstOrCreate(['name' => 'Secondaire']);

        // Créer les niveaux pour le primaire
        $cp1 = Level::firstOrCreate(['cycle_id' => $primaire->id, 'name' => 'CP1']);
        $cp2 = Level::firstOrCreate(['cycle_id' => $primaire->id, 'name' => 'CP2']);
        $ce1 = Level::firstOrCreate(['cycle_id' => $primaire->id, 'name' => 'CE1']);
        $ce2 = Level::firstOrCreate(['cycle_id' => $primaire->id, 'name' => 'CE2']);
        $cm1 = Level::firstOrCreate(['cycle_id' => $primaire->id, 'name' => 'CM1']);
        $cm2 = Level::firstOrCreate(['cycle_id' => $primaire->id, 'name' => 'CM2']);

        // Créer les niveaux pour le secondaire
        $sixieme = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => '6e']);
        $cinquieme = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => '5e']);
        $quatrieme = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => '4e']);
        $troisieme = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => '3e']);
        $seconde = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => '2nd']);
        $premiere = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => '1ère']);
        $terminale = Level::firstOrCreate(['cycle_id' => $secondaire->id, 'name' => 'Tle']);

        // Créer des classes pour l'année en cours
        $levels = [$cp1, $cp2, $ce1, $ce2, $cm1, $cm2, $sixieme, $cinquieme, $quatrieme, $troisieme, $seconde, $premiere, $terminale];
        
        foreach ($levels as $level) {
            // Créer 2 classes par niveau (A et B)
            foreach (['A', 'B'] as $section) {
                SchoolClass::updateOrCreate([
                    'academic_year_id' => $currentYear->id,
                    'level_id' => $level->id,
                    'name' => $section,
                ], [
                    'cycle_id' => $level->cycle_id,
                    'capacity' => rand(25, 35),
                ]);
            }
        }

        // Créer quelques classes supplémentaires pour certains niveaux populaires
        foreach ([$cp1, $sixieme, $seconde] as $level) {
            SchoolClass::updateOrCreate([
                'academic_year_id' => $currentYear->id,
                'level_id' => $level->id,
                'name' => 'C',
            ], [
                'cycle_id' => $level->cycle_id,
                'capacity' => 30,
            ]);
        }

        $this->command->info('Structure académique créée avec succès !');
        $this->command->info('- ' . AcademicYear::count() . ' années académiques');
        $this->command->info('- ' . Cycle::count() . ' cycles');
        $this->command->info('- ' . Level::count() . ' niveaux');
        $this->command->info('- ' . SchoolClass::count() . ' classes');
    }
}
