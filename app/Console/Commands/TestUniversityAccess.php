<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School;
use Illuminate\Support\Facades\Auth;

class TestUniversityAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:university-access {action=show} {school_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test university access restrictions for schools';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $schoolId = $this->argument('school_id');

        switch ($action) {
            case 'show':
                $this->showSchools();
                break;
            case 'set-university':
                if (!$schoolId) {
                    $this->error('School ID required for set-university action');
                    return;
                }
                $this->setSchoolAsUniversity($schoolId);
                break;
            case 'set-pre-university':
                if (!$schoolId) {
                    $this->error('School ID required for set-pre-university action');
                    return;
                }
                $this->setSchoolAsPreUniversity($schoolId);
                break;
            case 'test-access':
                if (!$schoolId) {
                    $this->error('School ID required for test-access action');
                    return;
                }
                $this->testAccessForSchool($schoolId);
                break;
            default:
                $this->error('Invalid action. Use: show, set-university, set-pre-university, test-access');
        }
    }

    private function showSchools()
    {
        $schools = School::all();
        $this->info('Liste des écoles:');
        $this->info('----------------');
        
        foreach ($schools as $school) {
            $type = $school->type ?? 'non défini';
            $isUniversity = $school->isUniversity() ? '✓ ACCÈS AUTORISÉ' : '✗ ACCÈS BLOQUÉ';
            $this->line("ID: {$school->id} | {$school->name} | Type: {$type} | Module Universitaire: {$isUniversity}");
        }
        
        $this->info('');
        $this->info('Usage:');
        $this->line('- Afficher les écoles: php artisan test:university-access show');
        $this->line('- Tester l\'accès: php artisan test:university-access test-access {id}');
        $this->line('- Définir comme université: php artisan test:university-access set-university {id}');
        $this->line('- Définir comme pré-universitaire: php artisan test:university-access set-pre-university {id}');
    }

    private function setSchoolAsUniversity($schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            $this->error('École non trouvée');
            return;
        }

        $oldType = $school->type;
        $school->update(['type' => 'university']);
        $this->info("École '{$school->name}' modifiée:");
        $this->line("- Type précédent: {$oldType}");
        $this->line("- Nouveau type: university");
        $this->line("- Accès module universitaire: ✓ AUTORISÉ");
    }

    private function setSchoolAsPreUniversity($schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            $this->error('École non trouvée');
            return;
        }

        $oldType = $school->type;
        $school->update(['type' => 'pre_university']);
        $this->info("École '{$school->name}' modifiée:");
        $this->line("- Type précédent: {$oldType}");
        $this->line("- Nouveau type: pre_university");
        $this->line("- Accès module universitaire: ✗ BLOQUÉ");
    }

    private function testAccessForSchool($schoolId)
    {
        $school = School::find($schoolId);
        if (!$school) {
            $this->error('École non trouvée');
            return;
        }

        $this->info("Test d'accès pour l'école: {$school->name}");
        $this->info('----------------------------------------');
        $this->line("ID: {$school->id}");
        $this->line("Nom: {$school->name}");
        $this->line("Type: {$school->type}");
        
        if ($school->isUniversity()) {
            $this->info('✓ ACCÈS AUTORISÉ au module universitaire');
            $this->line('- L\'école peut accéder à la gestion des UFRs');
            $this->line('- L\'école peut accéder à la gestion des départements');
            $this->line('- L\'école peut accéder à la gestion des programmes');
            $this->line('- Le menu université sera visible dans la barre latérale');
        } else {
            $this->error('✗ ACCÈS BLOQUÉ au module universitaire');
            $this->line('- Middleware bloquera l\'accès aux routes universitaires');
            $this->line('- Le menu université sera caché dans la barre latérale');
            $this->line('- Redirection vers le dashboard si tentative d\'accès');
        }
    }
}
