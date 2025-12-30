<?php

namespace App\Console\Commands;

use App\Models\TeacherAssignment;
use App\Models\Subject;
use Illuminate\Console\Command;

class AssignSubjectsToTeachers extends Command
{
    protected $signature = 'assign:subjects';
    protected $description = 'Assign subjects to teacher assignments';

    public function handle()
    {
        $this->info('Assignation des matières aux enseignants...');

        $mathSubject = Subject::where('code', 'MATH')->first();
        $frenchSubject = Subject::where('code', 'FRAN')->first();
        $historySubject = Subject::where('code', 'HIST')->first();
        $scienceSubject = Subject::where('code', 'SCI')->first();
        $englishSubject = Subject::where('code', 'ANG')->first();

        $subjects = [$mathSubject, $frenchSubject, $historySubject, $scienceSubject, $englishSubject];

        $assignments = TeacherAssignment::all();
        $this->info("Trouvé {$assignments->count()} affectations");

        foreach ($assignments as $i => $assignment) {
            $subject = $subjects[$i % count($subjects)];
            $assignment->subject_id = $subject->id;
            $assignment->save();

            $this->line("✓ Enseignant {$assignment->teacher_id} → Classe {$assignment->class_id} → {$subject->name}");
        }

        $this->info('Assignation terminée !');

        // Test des relations
        $this->info("\nTest des relations :");
        $assignment = TeacherAssignment::with(['teacher.user', 'schoolClass', 'subject'])->first();
        if ($assignment && $assignment->subject) {
            $teacherName = $assignment->teacher->user->name ?? 'N/A';
            $className = $assignment->schoolClass->name ?? 'N/A';
            $subjectName = $assignment->subject->name ?? 'N/A';
            
            $this->line("Exemple : {$teacherName} enseigne {$subjectName} en classe {$className}");
        }
    }
}