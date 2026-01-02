<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\UFR;

class UFRTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        
        if (!$school || $school->type !== 'university') {
            $this->command->error('Aucune école universitaire trouvée.');
            return;
        }

        // Créer quelques UFR de test
        $ufrs = [
            [
                'name' => 'Unité de Formation et de Recherche en Sciences et Technologies',
                'short_name' => 'UFR-ST',
                'code' => 'UFR-ST-2026',
                'description' => 'Formation et recherche dans les domaines scientifiques et technologiques',
                'dean_name' => 'Pr. Marie BERNARD',
                'contact_email' => 'ufr-st@ust.edu',
                'contact_phone' => '+33 1 23 45 67 90',
                'building' => 'Bâtiment Sciences - Campus Nord',
                'address' => '123 Avenue Universitaire, Bâtiment Sciences, 75001 Paris',
                'is_active' => true,
            ],
            [
                'name' => 'Unité de Formation et de Recherche en Sciences Humaines et Sociales',
                'short_name' => 'UFR-SHS',
                'code' => 'UFR-SHS-2026',
                'description' => 'Formation et recherche en sciences humaines, sociales et littéraires',
                'dean_name' => 'Pr. Jean-Pierre MARTIN',
                'contact_email' => 'ufr-shs@ust.edu',
                'contact_phone' => '+33 1 23 45 67 91',
                'building' => 'Bâtiment Lettres - Campus Central',
                'address' => '456 Avenue Universitaire, Bâtiment Lettres, 75001 Paris',
                'is_active' => true,
            ],
            [
                'name' => 'Unité de Formation et de Recherche en Économie et Gestion',
                'short_name' => 'UFR-EG',
                'code' => 'UFR-EG-2026',
                'description' => 'Formation et recherche en sciences économiques et gestion d\'entreprise',
                'dean_name' => 'Dr. Sarah DUBOIS',
                'contact_email' => 'ufr-eg@ust.edu',
                'contact_phone' => '+33 1 23 45 67 92',
                'building' => 'Bâtiment Économie - Campus Sud',
                'address' => '789 Avenue Universitaire, Bâtiment Économie, 75001 Paris',
                'is_active' => true,
            ],
        ];

        foreach ($ufrs as $ufrData) {
            $ufrData['school_id'] = $school->id;
            
            UFR::updateOrCreate(
                ['code' => $ufrData['code']],
                $ufrData
            );
        }

        $this->command->info('UFR de test créées avec succès.');
    }
}