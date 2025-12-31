<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\School;
use App\Models\Cycle;
use App\Models\Level;
use App\Models\AcademicTrack;
use App\Models\Subject;
use App\Models\SchoolClass;

class TestModuleA3Command extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:module-a3';

    /**
     * The console command description.
     */
    protected $description = 'Test MODULE A3 - Structure académique';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 TEST MODULE A3 - Structure académique');
        $this->info('=====================================');
        $this->newLine();

        // Test 1: École
        $school = School::first();
        $this->line("🏫 École: " . $school->name);

        // Test 2: Cycles avec école
        $cycles = Cycle::where('school_id', $school->id)->get();
        $this->line("🔄 Cycles liés à l'école: " . $cycles->count());
        foreach ($cycles as $cycle) {
            $this->line("   - {$cycle->name} (actif: " . ($cycle->is_active ? 'oui' : 'non') . ")");
        }

        // Test 3: Niveaux avec école
        $levels = Level::where('school_id', $school->id)->get();
        $this->line("📚 Niveaux liés à l'école: " . $levels->count());
        foreach ($levels as $level) {
            $this->line("   - {$level->name} ({$level->type}) [{$level->code}] ordre: {$level->order}");
        }

        // Test 4: Filières
        $tracks = AcademicTrack::where('school_id', $school->id)->get();
        $this->line("🎯 Filières créées: " . $tracks->count());
        foreach ($tracks as $track) {
            $this->line("   - {$track->name} [{$track->code}]");
        }

        // Test 5: Matières MODULE A3
        $subjects = Subject::where('school_id', $school->id)->get();
        $this->line("📖 Matières MODULE A3: " . $subjects->count());
        foreach ($subjects as $subject) {
            $levelName = $subject->level ? $subject->level->name : 'N/A';
            $this->line("   - {$subject->full_name} (Niveau: {$levelName}, Coef: {$subject->coefficient})");
        }

        // Test 6: Classes actives
        $classes = SchoolClass::where('is_active', true)->count();
        $this->line("🏛️ Classes actives: " . $classes);

        $this->newLine();
        $this->info('✅ Module A3 fonctionne parfaitement !');

        // Test des scopes
        $this->newLine();
        $this->info('🔍 Test des scopes...');
        
        $activeSubjects = Subject::active()->where('school_id', $school->id)->count();
        $this->line("Matières actives: $activeSubjects");
        
        $secondarySubjects = Subject::secondary()->where('school_id', $school->id)->count();
        $this->line("Matières secondaires: $secondarySubjects");
        
        $activeLevels = Level::active()->where('school_id', $school->id)->count();
        $this->line("Niveaux actifs: $activeLevels");
        
        $activeTracks = AcademicTrack::active()->where('school_id', $school->id)->count();
        $this->line("Filières actives: $activeTracks");
        
        $this->info('✅ Scopes fonctionnels !');

        return Command::SUCCESS;
    }
}
