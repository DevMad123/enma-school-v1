<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\School;

class UpdateSchoolTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        
        if ($school) {
            $school->update(['type' => 'university']);
            $this->command->info("École '{$school->name}' mise à jour en type universitaire.");
        } else {
            // Créer une école universitaire
            $school = School::create([
                'name' => 'Université des Sciences et Technologies',
                'type' => 'university',
                'short_name' => 'UST',
                'address' => '123 Avenue Universitaire, Campus Nord',
                'city' => 'Paris',
                'country' => 'France',
                'phone' => '+33 1 23 45 67 89',
                'email' => 'contact@ust.edu',
                'academic_system' => 'semestre',
                'grading_system' => '20',
                'is_active' => true,
            ]);
            $this->command->info("École universitaire '{$school->name}' créée avec succès.");
        }
    }
}