<?php

namespace App\Console\Commands;

use App\Models\Level;
use App\Models\Subject;
use Illuminate\Console\Command;

class TestSubjectRelations extends Command
{
    protected $signature = 'test:subjects';
    protected $description = 'Test subject-level relationships';

    public function handle()
    {
        $this->info('=== TEST DES RELATIONS MATIÈRES ===');

        // Test 1 : Récupérer un niveau et ses matières
        $this->info("\n1. Niveau CP1 et ses matières :");
        $level = Level::where('name', 'CP1')->first();
        if ($level) {
            $this->line("Niveau : {$level->name}");
            $this->line("Matières :");
            foreach ($level->subjects as $subject) {
                $this->line("  - {$subject->name} ({$subject->code})");
            }
        } else {
            $this->error("Niveau CP1 non trouvé");
        }

        // Test 2 : Récupérer une matière et ses niveaux
        $this->info("\n2. Matière Mathématiques et ses niveaux :");
        $subject = Subject::where('code', 'MATH')->first();
        if ($subject) {
            $this->line("Matière : {$subject->name} ({$subject->code})");
            $this->line("Coefficient : {$subject->coefficient}");
            $this->line("Niveaux :");
            foreach ($subject->levels as $level) {
                $this->line("  - {$level->name} ({$level->cycle->name})");
            }
        } else {
            $this->error("Matière Mathématiques non trouvée");
        }

        // Test 3 : Compter les relations
        $this->info("\n3. Statistiques :");
        $this->line("Total matières : " . Subject::count());
        $this->line("Total niveaux : " . Level::count());
        $this->line("Total relations niveau-matière : " . \DB::table('level_subject')->count());

        $this->info("\n=== TEST TERMINÉ ===");
    }
}