<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\UFR;

class SimpleUFRTestSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::first();
        
        if (!$school) {
            $this->command->error('Aucune école trouvée.');
            return;
        }

        UFR::create([
            'school_id' => $school->id,
            'name' => 'UFR Sciences et Technologies',
            'code' => 'UFR-ST-26',
            'short_name' => 'UFR-ST',
            'description' => 'Formation et recherche en sciences et technologies',
            'dean_name' => 'Pr. Marie BERNARD',
            'contact_email' => 'ufr-st@ust.edu',
            'contact_phone' => '+33 1 23 45 67 90',
            'building' => 'Bâtiment Sciences',
            'is_active' => true,
        ]);

        $this->command->info('UFR de test créée avec succès.');
    }
}