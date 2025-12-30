<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Level;
use App\Models\Cycle;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des matières...');

        // Définir les matières de base
        $subjects = [
            ['name' => 'Mathématiques', 'code' => 'MATH', 'coefficient' => 3],
            ['name' => 'Français', 'code' => 'FRAN', 'coefficient' => 3],
            ['name' => 'Histoire-Géographie', 'code' => 'HIST', 'coefficient' => 2],
            ['name' => 'Sciences', 'code' => 'SCI', 'coefficient' => 2],
            ['name' => 'Anglais', 'code' => 'ANG', 'coefficient' => 2],
            ['name' => 'Éducation Physique', 'code' => 'EPS', 'coefficient' => 1],
            ['name' => 'Arts Plastiques', 'code' => 'ART', 'coefficient' => 1],
            ['name' => 'Musique', 'code' => 'MUS', 'coefficient' => 1],
            ['name' => 'Philosophie', 'code' => 'PHIL', 'coefficient' => 3],
            ['name' => 'Physique-Chimie', 'code' => 'PHY', 'coefficient' => 3],
            ['name' => 'Sciences de la Vie et de la Terre', 'code' => 'SVT', 'coefficient' => 2],
            ['name' => 'Économie-Gestion', 'code' => 'ECON', 'coefficient' => 2],
            ['name' => 'Informatique', 'code' => 'INFO', 'coefficient' => 2],
        ];

        // Créer les matières
        foreach ($subjects as $subjectData) {
            $subject = Subject::updateOrCreate(
                ['code' => $subjectData['code']],
                $subjectData
            );
            $this->command->info("Matière créée : {$subject->name} ({$subject->code})");
        }

        // Associer les matières aux niveaux
        $this->associateSubjectsToLevels();

        $this->command->info('Matières créées et associées aux niveaux avec succès !');
        $this->command->info('- ' . Subject::count() . ' matières au total');
    }

    private function associateSubjectsToLevels()
    {
        $this->command->info('Association des matières aux niveaux...');

        // Obtenir les cycles et niveaux
        $primaire = Cycle::where('name', 'Primaire')->first();
        $secondaire = Cycle::where('name', 'Secondaire')->first();

        if (!$primaire || !$secondaire) {
            $this->command->error('Cycles non trouvés. Exécutez d\'abord AcademicStructureSeeder.');
            return;
        }

        $niveauxPrimaire = Level::where('cycle_id', $primaire->id)->get();
        $niveauxSecondaire = Level::where('cycle_id', $secondaire->id)->get();

        // Matières pour le primaire (CP1 à CM2)
        $matieresPrimaire = Subject::whereIn('code', ['MATH', 'FRAN', 'SCI', 'ANG', 'EPS', 'ART', 'MUS'])->get();
        
        foreach ($niveauxPrimaire as $niveau) {
            foreach ($matieresPrimaire as $matiere) {
                $niveau->subjects()->syncWithoutDetaching([$matiere->id]);
                $this->command->info("  → {$niveau->name} : {$matiere->name}");
            }
        }

        // Matières pour le secondaire (6e à Terminale)
        $matieresSecondaire = Subject::whereIn('code', [
            'MATH', 'FRAN', 'HIST', 'SCI', 'ANG', 'EPS', 'ART', 'MUS'
        ])->get();

        // Matières pour les niveaux lycée (2nd à Terminale)
        $matieresLycee = Subject::whereIn('code', [
            'MATH', 'FRAN', 'HIST', 'PHY', 'SVT', 'ANG', 'EPS', 'PHIL', 'ECON', 'INFO'
        ])->get();

        // Niveaux collège (6e à 3e)
        $niveauxCollege = Level::where('cycle_id', $secondaire->id)
            ->whereIn('name', ['6e', '5e', '4e', '3e'])->get();

        // Niveaux lycée (2nd à Terminale)
        $niveauxLycee = Level::where('cycle_id', $secondaire->id)
            ->whereIn('name', ['2nd', '1ère', 'Tle'])->get();

        // Associer matières au collège
        foreach ($niveauxCollege as $niveau) {
            foreach ($matieresSecondaire as $matiere) {
                $niveau->subjects()->syncWithoutDetaching([$matiere->id]);
                $this->command->info("  → {$niveau->name} : {$matiere->name}");
            }
        }

        // Associer matières au lycée (avec matières spécialisées)
        foreach ($niveauxLycee as $niveau) {
            foreach ($matieresLycee as $matiere) {
                $niveau->subjects()->syncWithoutDetaching([$matiere->id]);
                $this->command->info("  → {$niveau->name} : {$matiere->name}");
            }
        }

        // Philosophie uniquement en Terminale
        $terminale = Level::where('cycle_id', $secondaire->id)->where('name', 'Tle')->first();
        $philosophie = Subject::where('code', 'PHIL')->first();
        
        if ($terminale && $philosophie) {
            $terminale->subjects()->syncWithoutDetaching([$philosophie->id]);
            $this->command->info("  → Terminale : Philosophie (spéciale)");
        }
    }
}
