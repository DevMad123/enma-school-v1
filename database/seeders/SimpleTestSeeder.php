<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;

class SimpleTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Test avec un seul enregistrement
        $school = new School();
        $school->name = 'École Test Simple';
        $school->short_name = 'ETS';
        $school->type = 'secondary'; // Enum exact
        $school->email = 'test@example.ci';
        $school->phone = '+225 01 23 45 67 89';
        $school->address = '123 Test Avenue';
        $school->city = 'Abidjan';
        $school->country = 'Côte d\'Ivoire';
        $school->academic_system = 'trimestre';
        $school->grading_system = '20';
        $school->is_active = true;
        
        $school->save();
        
        $this->command->info('École test créée avec succès');
    }
}
