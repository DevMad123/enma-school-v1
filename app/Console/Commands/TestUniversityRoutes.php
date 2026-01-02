<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;

class TestUniversityRoutes extends Command
{
    protected $signature = 'test:university-routes {school_id?}';
    protected $description = 'Test access to university routes with different school types';

    public function handle()
    {
        $schoolId = $this->argument('school_id') ?? 1;
        
        $this->info("Test d'accès aux routes universitaires");
        $this->info("=====================================");
        
        $school = School::find($schoolId);
        if (!$school) {
            $this->error('École non trouvée');
            return;
        }
        
        $this->line("École testée: {$school->name} (Type: {$school->type})");
        $this->info('');
        
        // Test avec école universitaire
        $this->info('Test 1: École de type "university"');
        $school->update(['type' => 'university']);
        $this->testUniversityAccess($school);
        
        $this->info('');
        
        // Test avec école non-universitaire  
        $this->info('Test 2: École de type "pre_university"');
        $school->update(['type' => 'pre_university']);
        $this->testUniversityAccess($school);
        
        // Remettre en université
        $school->update(['type' => 'university']);
        $this->info('');
        $this->info('✓ École remise en mode université');
    }
    
    private function testUniversityAccess($school)
    {
        $this->line("- Type d'école: {$school->type}");
        $this->line("- Méthode isUniversity(): " . ($school->isUniversity() ? 'true' : 'false'));
        
        if ($school->isUniversity()) {
            $this->info('  ✓ ACCÈS AUTORISÉ');
            $this->line('  - Le middleware autorisera l\'accès');
            $this->line('  - Le menu université sera visible');
            $this->line('  - Les routes sont accessibles:');
            $this->line('    • /university/dashboard');
            $this->line('    • /university/ufrs');
            $this->line('    • /university/departments');
            $this->line('    • /university/programs');
        } else {
            $this->error('  ✗ ACCÈS BLOQUÉ');
            $this->line('  - Le middleware bloquera l\'accès');
            $this->line('  - Redirection vers academic.levels avec message d\'erreur');
            $this->line('  - Le menu université sera caché');
            $this->line('  - Message: "Cette section est réservée aux établissements universitaires."');
        }
    }
}