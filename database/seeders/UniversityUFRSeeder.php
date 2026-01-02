<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\UFR;

class UniversityUFRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier qu'il y a une école universitaire
        $school = School::where('type', 'university')->first();
        
        if (!$school) {
            $this->command->error('Aucune école universitaire trouvée. Créez d\'abord une école avec type = "university".');
            return;
        }

        $this->command->info("Création des UFR pour l'école universitaire : {$school->name}");

        $ufrs = [
            [
                'name' => 'Unité de Formation et de Recherche en Sciences et Technologies',
                'short_name' => 'UFR-ST',
                'code' => 'UFR-ST-001',
                'description' => 'Formation et recherche dans les domaines scientifiques et technologiques : informatique, mathématiques, physique, chimie, biologie et ingénierie.',
                'dean_name' => 'Pr. Marie BERNARD',
                'contact_email' => 'ufr.st@universite.edu',
                'contact_phone' => '+225 01 23 45 67 89',
                'building' => 'Bâtiment Sciences - Campus Nord',
                'address' => 'Avenue de l\'Université, Campus Nord, Abidjan',
                'is_active' => true,
            ],
            [
                'name' => 'Unité de Formation et de Recherche en Sciences Humaines et Sociales',
                'short_name' => 'UFR-SHS',
                'code' => 'UFR-SHS-002',
                'description' => 'Formation et recherche en sciences humaines, sociales et littéraires : lettres, langues, philosophie, psychologie, sociologie, géographie et histoire.',
                'dean_name' => 'Pr. Jean-Baptiste KOUASSI',
                'contact_email' => 'ufr.shs@universite.edu',
                'contact_phone' => '+225 01 23 45 67 90',
                'building' => 'Bâtiment Lettres et Sciences Humaines - Campus Central',
                'address' => 'Avenue de l\'Université, Campus Central, Abidjan',
                'is_active' => true,
            ],
            [
                'name' => 'Unité de Formation et de Recherche en Économie et Gestion',
                'short_name' => 'UFR-EG',
                'code' => 'UFR-EG-003',
                'description' => 'Formation et recherche en sciences économiques, gestion d\'entreprise, finance, marketing et management.',
                'dean_name' => 'Dr. Awa TRAORÉ',
                'contact_email' => 'ufr.eg@universite.edu',
                'contact_phone' => '+225 01 23 45 67 91',
                'building' => 'Bâtiment Économie et Gestion - Campus Sud',
                'address' => 'Avenue de l\'Université, Campus Sud, Abidjan',
                'is_active' => true,
            ],
            [
                'name' => 'Unité de Formation et de Recherche en Médecine et Sciences de la Santé',
                'short_name' => 'UFR-MSS',
                'code' => 'UFR-MSS-004',
                'description' => 'Formation et recherche dans le domaine médical et des sciences de la santé : médecine, pharmacie, odontologie, sciences infirmières.',
                'dean_name' => 'Pr. Dr. Michel KONE',
                'contact_email' => 'ufr.mss@universite.edu',
                'contact_phone' => '+225 01 23 45 67 92',
                'building' => 'Centre Hospitalier Universitaire - Campus Médical',
                'address' => 'Boulevard de la Santé, Campus Médical, Abidjan',
                'is_active' => true,
            ],
            [
                'name' => 'Unité de Formation et de Recherche en Droit et Sciences Politiques',
                'short_name' => 'UFR-DSP',
                'code' => 'UFR-DSP-005',
                'description' => 'Formation et recherche en droit, sciences politiques, administration publique et relations internationales.',
                'dean_name' => 'Pr. Fatou DIALLO',
                'contact_email' => 'ufr.dsp@universite.edu',
                'contact_phone' => '+225 01 23 45 67 93',
                'building' => 'Bâtiment Droit et Sciences Politiques - Campus Ouest',
                'address' => 'Avenue de l\'Université, Campus Ouest, Abidjan',
                'is_active' => true,
            ],
        ];

        foreach ($ufrs as $ufrData) {
            $ufrData['school_id'] = $school->id;
            
            $ufr = UFR::updateOrCreate(
                ['code' => $ufrData['code']],
                $ufrData
            );
            
            $this->command->info("✅ UFR créée : {$ufr->name} ({$ufr->code})");
        }

        $totalUFRs = UFR::where('school_id', $school->id)->count();
        $this->command->info("🎓 Total des UFR : {$totalUFRs} UFR créées avec succès.");
    }
}