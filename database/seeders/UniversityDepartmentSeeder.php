<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\UFR;
use App\Models\Department;

class UniversityDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier qu'il y a une école universitaire avec des UFR
        $school = School::where('type', 'university')->first();
        
        if (!$school) {
            $this->command->error('Aucune école universitaire trouvée.');
            return;
        }

        $ufrs = UFR::where('school_id', $school->id)->get();
        if ($ufrs->isEmpty()) {
            $this->command->error('Aucune UFR trouvée. Exécutez d\'abord UniversityUFRSeeder.');
            return;
        }

        $this->command->info("Création des départements pour l'école universitaire : {$school->name}");

        // Départements par UFR
        $departmentsByUFR = [
            'UFR-ST-001' => [
                [
                    'name' => 'Département d\'Informatique et Technologies du Numérique',
                    'short_name' => 'INFO',
                    'code' => 'DEPT-INFO-001',
                    'description' => 'Formation en informatique, génie logiciel, intelligence artificielle, cybersécurité et technologies émergentes.',
                    'head_of_department' => 'Dr. Yao ASSOUMOU',
                    'contact_email' => 'info@ufr-st.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 01',
                    'office_location' => 'Bureau 201 - Bâtiment Sciences',
                ],
                [
                    'name' => 'Département de Mathématiques et Statistiques',
                    'short_name' => 'MATH',
                    'code' => 'DEPT-MATH-002',
                    'description' => 'Formation en mathématiques pures et appliquées, statistiques, recherche opérationnelle et modélisation.',
                    'head_of_department' => 'Pr. Aminata BAMBA',
                    'contact_email' => 'math@ufr-st.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 02',
                    'office_location' => 'Bureau 301 - Bâtiment Sciences',
                ],
                [
                    'name' => 'Département de Physique et Sciences de l\'Ingénieur',
                    'short_name' => 'PHYS',
                    'code' => 'DEPT-PHYS-003',
                    'description' => 'Formation en physique fondamentale et appliquée, génie électrique, mécanique et énergétique.',
                    'head_of_department' => 'Dr. Kouame N\'GUESSAN',
                    'contact_email' => 'physique@ufr-st.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 03',
                    'office_location' => 'Bureau 401 - Bâtiment Sciences',
                ],
                [
                    'name' => 'Département de Chimie et Biochimie',
                    'short_name' => 'CHIM',
                    'code' => 'DEPT-CHIM-004',
                    'description' => 'Formation en chimie générale, organique, analytique et biochimie appliquée.',
                    'head_of_department' => 'Dr. Mariam SANOGO',
                    'contact_email' => 'chimie@ufr-st.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 04',
                    'office_location' => 'Bureau 501 - Bâtiment Sciences',
                ],
            ],
            'UFR-SHS-002' => [
                [
                    'name' => 'Département de Lettres Modernes et Linguistique',
                    'short_name' => 'LETT',
                    'code' => 'DEPT-LETT-005',
                    'description' => 'Formation en lettres modernes, linguistique, littérature française et francophone.',
                    'head_of_department' => 'Pr. Adjoua DIABATE',
                    'contact_email' => 'lettres@ufr-shs.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 05',
                    'office_location' => 'Bureau 102 - Bâtiment Lettres',
                ],
                [
                    'name' => 'Département de Philosophie et Sciences Cognitives',
                    'short_name' => 'PHIL',
                    'code' => 'DEPT-PHIL-006',
                    'description' => 'Formation en philosophie, éthique, logique et sciences cognitives.',
                    'head_of_department' => 'Dr. Sékou COULIBALY',
                    'contact_email' => 'philosophie@ufr-shs.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 06',
                    'office_location' => 'Bureau 202 - Bâtiment Lettres',
                ],
                [
                    'name' => 'Département d\'Histoire et Géographie',
                    'short_name' => 'HIST',
                    'code' => 'DEPT-HIST-007',
                    'description' => 'Formation en histoire, géographie, archéologie et patrimoine culturel.',
                    'head_of_department' => 'Pr. Akissi KOUADIO',
                    'contact_email' => 'histoire@ufr-shs.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 07',
                    'office_location' => 'Bureau 302 - Bâtiment Lettres',
                ],
                [
                    'name' => 'Département de Psychologie et Sociologie',
                    'short_name' => 'PSYC',
                    'code' => 'DEPT-PSYC-008',
                    'description' => 'Formation en psychologie clinique, sociale, du travail et sociologie.',
                    'head_of_department' => 'Dr. Raissa OUATTARA',
                    'contact_email' => 'psychologie@ufr-shs.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 08',
                    'office_location' => 'Bureau 402 - Bâtiment Lettres',
                ],
            ],
            'UFR-EG-003' => [
                [
                    'name' => 'Département d\'Économie et Politique Économique',
                    'short_name' => 'ECO',
                    'code' => 'DEPT-ECO-009',
                    'description' => 'Formation en sciences économiques, politique économique, économétrie et développement.',
                    'head_of_department' => 'Pr. Adama OUEDRAOGO',
                    'contact_email' => 'economie@ufr-eg.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 09',
                    'office_location' => 'Bureau 101 - Bâtiment Économie',
                ],
                [
                    'name' => 'Département de Gestion et Administration des Entreprises',
                    'short_name' => 'GEST',
                    'code' => 'DEPT-GEST-010',
                    'description' => 'Formation en management, gestion des ressources humaines, stratégie d\'entreprise.',
                    'head_of_department' => 'Dr. Fatoumata KONE',
                    'contact_email' => 'gestion@ufr-eg.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 10',
                    'office_location' => 'Bureau 201 - Bâtiment Économie',
                ],
                [
                    'name' => 'Département de Finance et Comptabilité',
                    'short_name' => 'FIN',
                    'code' => 'DEPT-FIN-011',
                    'description' => 'Formation en finance d\'entreprise, comptabilité, audit et contrôle de gestion.',
                    'head_of_department' => 'Dr. Ibrahim SANGARE',
                    'contact_email' => 'finance@ufr-eg.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 11',
                    'office_location' => 'Bureau 301 - Bâtiment Économie',
                ],
                [
                    'name' => 'Département de Marketing et Communication',
                    'short_name' => 'MARK',
                    'code' => 'DEPT-MARK-012',
                    'description' => 'Formation en marketing, communication digitale, publicité et relations publiques.',
                    'head_of_department' => 'Dr. Yasmin BARRY',
                    'contact_email' => 'marketing@ufr-eg.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 12',
                    'office_location' => 'Bureau 401 - Bâtiment Économie',
                ],
            ],
            'UFR-MSS-004' => [
                [
                    'name' => 'Département de Médecine Générale et Spécialisée',
                    'short_name' => 'MED',
                    'code' => 'DEPT-MED-013',
                    'description' => 'Formation médicale complète : médecine générale, spécialités médicales et chirurgicales.',
                    'head_of_department' => 'Pr. Dr. Kofi ASANTE',
                    'contact_email' => 'medecine@ufr-mss.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 13',
                    'office_location' => 'Bureau 501 - Centre Hospitalier Universitaire',
                ],
                [
                    'name' => 'Département de Pharmacie et Sciences Biomédicales',
                    'short_name' => 'PHAR',
                    'code' => 'DEPT-PHAR-014',
                    'description' => 'Formation en pharmacie, sciences biomédicales, toxicologie et pharmacologie.',
                    'head_of_department' => 'Dr. Aïcha DIARRA',
                    'contact_email' => 'pharmacie@ufr-mss.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 14',
                    'office_location' => 'Bureau 601 - Centre Hospitalier Universitaire',
                ],
                [
                    'name' => 'Département de Sciences Infirmières et Obstétricales',
                    'short_name' => 'SOIN',
                    'code' => 'DEPT-SOIN-015',
                    'description' => 'Formation en sciences infirmières, sage-femme et techniques de soins.',
                    'head_of_department' => 'Dr. Marie SAWADOGO',
                    'contact_email' => 'soins@ufr-mss.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 15',
                    'office_location' => 'Bureau 701 - Centre Hospitalier Universitaire',
                ],
            ],
            'UFR-DSP-005' => [
                [
                    'name' => 'Département de Droit Privé et Public',
                    'short_name' => 'DROIT',
                    'code' => 'DEPT-DROIT-016',
                    'description' => 'Formation en droit privé, public, international et européen.',
                    'head_of_department' => 'Pr. Moussa TRAORE',
                    'contact_email' => 'droit@ufr-dsp.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 16',
                    'office_location' => 'Bureau 101 - Bâtiment Droit',
                ],
                [
                    'name' => 'Département de Sciences Politiques et Relations Internationales',
                    'short_name' => 'SCPO',
                    'code' => 'DEPT-SCPO-017',
                    'description' => 'Formation en sciences politiques, relations internationales et diplomatie.',
                    'head_of_department' => 'Dr. Salimata KONATE',
                    'contact_email' => 'sciencespo@ufr-dsp.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 17',
                    'office_location' => 'Bureau 201 - Bâtiment Droit',
                ],
                [
                    'name' => 'Département d\'Administration Publique et Gouvernance',
                    'short_name' => 'ADM',
                    'code' => 'DEPT-ADM-018',
                    'description' => 'Formation en administration publique, gouvernance et politiques publiques.',
                    'head_of_department' => 'Dr. Bakary FOFANA',
                    'contact_email' => 'administration@ufr-dsp.universite.edu',
                    'contact_phone' => '+225 01 34 56 78 18',
                    'office_location' => 'Bureau 301 - Bâtiment Droit',
                ],
            ],
        ];

        $totalCreated = 0;

        foreach ($departmentsByUFR as $ufrCode => $departments) {
            $ufr = $ufrs->firstWhere('code', $ufrCode);
            
            if (!$ufr) {
                $this->command->warn("UFR avec le code {$ufrCode} non trouvée. Départements ignorés.");
                continue;
            }

            $this->command->info("📂 Création des départements pour UFR : {$ufr->name}");

            foreach ($departments as $departmentData) {
                $departmentData['school_id'] = $school->id;
                $departmentData['ufr_id'] = $ufr->id;
                $departmentData['is_active'] = true;

                $department = Department::updateOrCreate(
                    ['code' => $departmentData['code']],
                    $departmentData
                );

                $this->command->info("  ✅ Département créé : {$department->name} ({$department->code})");
                $totalCreated++;
            }
        }

        $this->command->info("🏢 Total des départements : {$totalCreated} départements créés avec succès.");
    }
}