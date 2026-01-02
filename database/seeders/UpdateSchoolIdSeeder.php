<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UpdateSchoolIdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🔄 Mise à jour des school_id pour les données existantes...');
        
        // Récupérer l'école active
        $activeSchool = School::getActiveSchool();
        
        if (!$activeSchool) {
            $this->command->error('❌ Aucune école active trouvée. Veuillez d\'abord créer une école.');
            return;
        }
        
        $this->command->info("📚 École active trouvée: {$activeSchool->name}");
        
        // Mettre à jour les étudiants sans school_id
        $studentsUpdated = Student::whereNull('school_id')->update(['school_id' => $activeSchool->id]);
        $this->command->info("👨‍🎓 {$studentsUpdated} étudiant(s) mis à jour");
        
        // Mettre à jour les classes sans school_id
        $classesUpdated = SchoolClass::whereNull('school_id')->update(['school_id' => $activeSchool->id]);
        $this->command->info("🏫 {$classesUpdated} classe(s) mise(s) à jour");
        
        $this->command->info('✅ Mise à jour terminée avec succès!');
        
        // Afficher un résumé
        $this->command->table(
            ['Entité', 'Total', 'Avec school_id'],
            [
                ['Étudiants', Student::count(), Student::whereNotNull('school_id')->count()],
                ['Classes', SchoolClass::count(), SchoolClass::whereNotNull('school_id')->count()],
            ]
        );
    }
}
