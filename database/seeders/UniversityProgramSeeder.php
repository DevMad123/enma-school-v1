<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Department;
use App\Models\Program;

class UniversityProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Vérifier qu'il y a une école universitaire avec des départements
        $school = School::where('type', 'university')->first();
        
        if (!$school) {
            $this->command->error('Aucune école universitaire trouvée.');
            return;
        }

        $departments = Department::where('school_id', $school->id)->get();
        if ($departments->isEmpty()) {
            $this->command->error('Aucun département trouvé. Exécutez d\'abord UniversityDepartmentSeeder.');
            return;
        }

        $this->command->info("Création des programmes pour l'école universitaire : {$school->name}");

        // Programmes par département
        $programsByDepartment = [
            'DEPT-INFO-001' => [
                [
                    'name' => 'Licence en Informatique Fondamentale',
                    'short_name' => 'L-INFO',
                    'code' => 'PROG-L-INFO-001',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation fondamentale en informatique couvrant les bases théoriques et pratiques.',
                    'objectives' => [
                        'Maîtriser les concepts fondamentaux de l\'informatique',
                        'Développer des compétences en programmation',
                        'Comprendre les structures de données et algorithmes',
                        'Acquérir des bases en systèmes et réseaux'
                    ],
                    'diploma_title' => 'Licence en Informatique Fondamentale',
                ],
                [
                    'name' => 'Master en Intelligence Artificielle',
                    'short_name' => 'M-IA',
                    'code' => 'PROG-M-IA-002',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en intelligence artificielle et apprentissage automatique.',
                    'objectives' => [
                        'Maîtriser les algorithmes d\'IA',
                        'Développer des systèmes intelligents',
                        'Comprendre l\'apprentissage automatique',
                        'Appliquer l\'IA dans différents domaines'
                    ],
                    'diploma_title' => 'Master en Intelligence Artificielle',
                ],
                [
                    'name' => 'Master en Cybersécurité et Sécurité des Systèmes',
                    'short_name' => 'M-CYBER',
                    'code' => 'PROG-M-CYBER-003',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en cybersécurité, sécurité des réseaux et des systèmes.',
                    'objectives' => [
                        'Maîtriser les techniques de sécurisation',
                        'Analyser et prévenir les cyberattaques',
                        'Gérer la sécurité des infrastructures',
                        'Développer des politiques de sécurité'
                    ],
                    'diploma_title' => 'Master en Cybersécurité et Sécurité des Systèmes',
                ],
            ],
            'DEPT-MATH-002' => [
                [
                    'name' => 'Licence en Mathématiques Pures',
                    'short_name' => 'L-MATH',
                    'code' => 'PROG-L-MATH-004',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation fondamentale en mathématiques pures et applications.',
                    'objectives' => [
                        'Maîtriser les concepts mathématiques fondamentaux',
                        'Développer le raisonnement logique',
                        'Résoudre des problèmes complexes',
                        'Préparer à la recherche mathématique'
                    ],
                    'diploma_title' => 'Licence en Mathématiques Pures',
                ],
                [
                    'name' => 'Master en Statistiques et Data Science',
                    'short_name' => 'M-STATS',
                    'code' => 'PROG-M-STATS-005',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en statistiques, analyse de données et science des données.',
                    'objectives' => [
                        'Maîtriser les méthodes statistiques avancées',
                        'Analyser des données complexes',
                        'Utiliser les outils de data science',
                        'Développer des modèles prédictifs'
                    ],
                    'diploma_title' => 'Master en Statistiques et Data Science',
                ],
            ],
            'DEPT-ECO-009' => [
                [
                    'name' => 'Licence en Sciences Économiques',
                    'short_name' => 'L-ECO',
                    'code' => 'PROG-L-ECO-006',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation fondamentale en sciences économiques et théories économiques.',
                    'objectives' => [
                        'Comprendre les mécanismes économiques',
                        'Analyser les politiques économiques',
                        'Maîtriser l\'économétrie',
                        'Développer l\'esprit analytique'
                    ],
                    'diploma_title' => 'Licence en Sciences Économiques',
                ],
                [
                    'name' => 'Master en Économie du Développement',
                    'short_name' => 'M-ECO-DEV',
                    'code' => 'PROG-M-ECO-DEV-007',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en économie du développement et politiques publiques.',
                    'objectives' => [
                        'Analyser les enjeux de développement',
                        'Concevoir des politiques de développement',
                        'Évaluer les programmes publics',
                        'Comprendre les économies émergentes'
                    ],
                    'diploma_title' => 'Master en Économie du Développement',
                ],
            ],
            'DEPT-GEST-010' => [
                [
                    'name' => 'Licence en Administration des Entreprises',
                    'short_name' => 'L-AE',
                    'code' => 'PROG-L-AE-008',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation généraliste en gestion et administration des entreprises.',
                    'objectives' => [
                        'Comprendre le fonctionnement des entreprises',
                        'Maîtriser les outils de gestion',
                        'Développer les compétences managériales',
                        'Acquérir une vision stratégique'
                    ],
                    'diploma_title' => 'Licence en Administration des Entreprises',
                ],
                [
                    'name' => 'Master en Management et Stratégie d\'Entreprise',
                    'short_name' => 'M-MSE',
                    'code' => 'PROG-M-MSE-009',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation avancée en management stratégique et direction d\'entreprise.',
                    'objectives' => [
                        'Élaborer des stratégies d\'entreprise',
                        'Diriger des équipes et projets',
                        'Analyser l\'environnement concurrentiel',
                        'Gérer la transformation organisationnelle'
                    ],
                    'diploma_title' => 'Master en Management et Stratégie d\'Entreprise',
                ],
                [
                    'name' => 'Master en Gestion des Ressources Humaines',
                    'short_name' => 'M-GRH',
                    'code' => 'PROG-M-GRH-010',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en gestion des ressources humaines et relations sociales.',
                    'objectives' => [
                        'Gérer le capital humain',
                        'Développer les politiques RH',
                        'Manager les relations sociales',
                        'Optimiser les performances humaines'
                    ],
                    'diploma_title' => 'Master en Gestion des Ressources Humaines',
                ],
            ],
            'DEPT-MED-013' => [
                [
                    'name' => 'Diplôme de Docteur en Médecine',
                    'short_name' => 'MD',
                    'code' => 'PROG-MD-011',
                    'level' => 'doctorat',
                    'duration_semesters' => 12,
                    'total_credits' => 360,
                    'description' => 'Formation complète en médecine générale et spécialisée.',
                    'objectives' => [
                        'Diagnostiquer et traiter les pathologies',
                        'Maîtriser les techniques médicales',
                        'Développer l\'approche clinique',
                        'Acquérir l\'éthique médicale'
                    ],
                    'diploma_title' => 'Docteur en Médecine',
                ],
            ],
            'DEPT-DROIT-016' => [
                [
                    'name' => 'Licence en Droit Général',
                    'short_name' => 'L-DROIT',
                    'code' => 'PROG-L-DROIT-012',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation fondamentale en droit privé, public et international.',
                    'objectives' => [
                        'Maîtriser les principes juridiques',
                        'Analyser les textes législatifs',
                        'Rédiger des actes juridiques',
                        'Comprendre le système judiciaire'
                    ],
                    'diploma_title' => 'Licence en Droit Général',
                ],
                [
                    'name' => 'Master en Droit International et Européen',
                    'short_name' => 'M-DIE',
                    'code' => 'PROG-M-DIE-013',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en droit international, européen et comparé.',
                    'objectives' => [
                        'Maîtriser le droit international',
                        'Comprendre les systèmes juridiques',
                        'Analyser les conventions internationales',
                        'Développer l\'expertise juridique'
                    ],
                    'diploma_title' => 'Master en Droit International et Européen',
                ],
            ],
            'DEPT-LETT-005' => [
                [
                    'name' => 'Licence en Lettres Modernes',
                    'short_name' => 'L-LETT',
                    'code' => 'PROG-L-LETT-014',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation en littérature française, francophone et linguistique.',
                    'objectives' => [
                        'Analyser les œuvres littéraires',
                        'Maîtriser la langue française',
                        'Développer l\'expression écrite',
                        'Comprendre les courants littéraires'
                    ],
                    'diploma_title' => 'Licence en Lettres Modernes',
                ],
                [
                    'name' => 'Master en Linguistique et Sciences du Langage',
                    'short_name' => 'M-LING',
                    'code' => 'PROG-M-LING-015',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en linguistique théorique et appliquée.',
                    'objectives' => [
                        'Analyser les structures linguistiques',
                        'Comprendre l\'évolution des langues',
                        'Développer la recherche linguistique',
                        'Maîtriser les outils d\'analyse'
                    ],
                    'diploma_title' => 'Master en Linguistique et Sciences du Langage',
                ],
            ],
            'DEPT-PSYC-008' => [
                [
                    'name' => 'Licence en Psychologie',
                    'short_name' => 'L-PSYC',
                    'code' => 'PROG-L-PSYC-016',
                    'level' => 'licence',
                    'duration_semesters' => 6,
                    'total_credits' => 180,
                    'description' => 'Formation fondamentale en psychologie générale, sociale et clinique.',
                    'objectives' => [
                        'Comprendre le comportement humain',
                        'Maîtriser les méthodes d\'évaluation',
                        'Développer l\'écoute active',
                        'Acquérir l\'éthique professionnelle'
                    ],
                    'diploma_title' => 'Licence en Psychologie',
                ],
                [
                    'name' => 'Master en Psychologie Clinique',
                    'short_name' => 'M-PSYC-CLIN',
                    'code' => 'PROG-M-PSYC-CLIN-017',
                    'level' => 'master',
                    'duration_semesters' => 4,
                    'total_credits' => 120,
                    'description' => 'Formation spécialisée en psychologie clinique et psychopathologie.',
                    'objectives' => [
                        'Diagnostiquer les troubles psychiques',
                        'Pratiquer la psychothérapie',
                        'Évaluer les fonctions cognitives',
                        'Accompagner les patients'
                    ],
                    'diploma_title' => 'Master en Psychologie Clinique',
                ],
            ],
        ];

        $totalCreated = 0;

        foreach ($programsByDepartment as $departmentCode => $programs) {
            $department = $departments->firstWhere('code', $departmentCode);
            
            if (!$department) {
                $this->command->warn("Département avec le code {$departmentCode} non trouvé. Programmes ignorés.");
                continue;
            }

            $this->command->info("📚 Création des programmes pour Département : {$department->name}");

            foreach ($programs as $programData) {
                $programData['school_id'] = $school->id;
                $programData['department_id'] = $department->id;
                $programData['is_active'] = true;

                $program = Program::updateOrCreate(
                    ['code' => $programData['code']],
                    $programData
                );

                $this->command->info("  ✅ Programme créé : {$program->name} ({$program->code}) - {$program->level}");
                $totalCreated++;
            }
        }

        $this->command->info("🎓 Total des programmes : {$totalCreated} programmes créés avec succès.");
        
        // Affichage des statistiques finales
        $licences = Program::where('school_id', $school->id)->where('level', 'licence')->count();
        $masters = Program::where('school_id', $school->id)->where('level', 'master')->count();
        $doctorats = Program::where('school_id', $school->id)->where('level', 'doctorat')->count();
        
        $this->command->info("📊 Répartition par niveau :");
        $this->command->info("   - Licences : {$licences}");
        $this->command->info("   - Masters : {$masters}");
        $this->command->info("   - Doctorats : {$doctorats}");
    }
}