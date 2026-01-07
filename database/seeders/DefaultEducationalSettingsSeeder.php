<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DefaultEducationalSetting;

class DefaultEducationalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Paramètres par défaut pour le préuniversitaire
        $this->seedPreUniversityDefaults();
        
        // Paramètres par défaut pour l'universitaire
        $this->seedUniversityDefaults();
    }

    /**
     * Paramètres par défaut pour le préuniversitaire
     */
    private function seedPreUniversityDefaults(): void
    {
        $preunivSettings = [
            // Limites d'âge
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'age_limits',
                'setting_key' => 'general',
                'setting_value' => [
                    'prescolaire' => ['min' => 3, 'max' => 6],
                    'primaire' => ['min' => 6, 'max' => 12],
                    'college' => ['min' => 12, 'max' => 16],
                    'lycee' => ['min' => 16, 'max' => 20],
                ],
                'description' => 'Limites d\'âge par niveau préuniversitaire',
                'is_required' => true,
                'validation_rules' => [
                    'required' => true,
                ],
            ],
            
            // Documents requis de base
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'documents',
                'setting_key' => 'required_enrollment',
                'setting_value' => [
                    'Acte de naissance',
                    'Certificat médical',
                    'Photo d\'identité',
                    'Bulletin de l\'année précédente',
                ],
                'description' => 'Documents de base requis pour toute inscription préuniversitaire',
                'is_required' => true,
            ],
            
            // Documents spécifiques par niveau
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'documents',
                'setting_key' => 'preuniversity',
                'setting_value' => [
                    'prescolaire' => ['Carnet de vaccinations'],
                    'primaire' => ['Certificat de fin de préscolaire'],
                    'college' => ['Certificat d\'études primaires (CEP)'],
                    'lycee' => ['Diplôme BEPC ou équivalent'],
                ],
                'description' => 'Documents spécifiques requis par niveau préuniversitaire',
                'is_required' => true,
            ],
            
            // Seuils d'évaluation
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'evaluation',
                'setting_key' => 'thresholds',
                'setting_value' => [
                    'excellent' => 16.0,
                    'tres_bien' => 14.0,
                    'bien' => 12.0,
                    'assez_bien' => 10.0,
                    'passable' => 8.0,
                    'echec' => 0.0,
                ],
                'description' => 'Seuils d\'évaluation du système éducatif ivoirien',
                'is_required' => true,
                'validation_rules' => [
                    'required' => true,
                ],
            ],
            
            // Structure des frais
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'fees',
                'setting_key' => 'structure',
                'setting_value' => [
                    'prescolaire' => [
                        'inscription' => 50000,
                        'scolarite_mensuelle' => 25000,
                        'uniforme' => 15000,
                        'fournitures' => 10000,
                    ],
                    'primaire' => [
                        'inscription' => 75000,
                        'scolarite_mensuelle' => 35000,
                        'uniforme' => 20000,
                        'fournitures' => 15000,
                    ],
                    'college' => [
                        'inscription' => 100000,
                        'scolarite_mensuelle' => 50000,
                        'uniforme' => 25000,
                        'fournitures' => 25000,
                    ],
                    'lycee' => [
                        'inscription' => 125000,
                        'scolarite_mensuelle' => 65000,
                        'uniforme' => 30000,
                        'fournitures' => 35000,
                    ],
                ],
                'description' => 'Structure des frais scolaires en FCFA',
                'is_required' => false,
                'validation_rules' => [
                    'min' => 0,
                ],
            ],
            
            // Coefficients des matières
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'subjects',
                'setting_key' => 'coefficients',
                'setting_value' => [
                    'primaire' => [
                        'francais' => 3,
                        'mathematiques' => 3,
                        'histoire_geo' => 2,
                        'sciences' => 2,
                        'education_civique' => 1,
                        'eps' => 1,
                    ],
                    'college' => [
                        'francais' => 4,
                        'mathematiques' => 4,
                        'anglais' => 3,
                        'histoire_geo' => 3,
                        'sciences_physiques' => 2,
                        'svt' => 2,
                        'education_civique' => 1,
                        'eps' => 1,
                    ],
                ],
                'description' => 'Coefficients des matières par niveau',
                'is_required' => true,
                'validation_rules' => [
                    'min' => 1,
                    'max' => 10,
                ],
            ],
            
            // Règles de passage
            [
                'school_type' => 'preuniversity',
                'educational_level' => null,
                'setting_category' => 'promotion',
                'setting_key' => 'rules',
                'setting_value' => [
                    'moyenne_generale_requise' => 10.0,
                    'moyenne_francais_maths_requise' => 8.0,
                    'nombre_matieres_eliminatoires_max' => 3,
                    'note_eliminatoire_seuil' => 5.0,
                    'possibilite_rachat' => [
                        'enabled' => true,
                        'deficit_max' => 2.0,
                    ],
                    'redoublement_max_consecutif' => 2,
                ],
                'description' => 'Règles de passage de classe',
                'is_required' => true,
                'validation_rules' => [
                    'required' => true,
                ],
            ],
        ];

        foreach ($preunivSettings as $setting) {
            DefaultEducationalSetting::updateOrCreate(
                [
                    'school_type' => $setting['school_type'],
                    'educational_level' => $setting['educational_level'],
                    'setting_category' => $setting['setting_category'],
                    'setting_key' => $setting['setting_key'],
                ],
                $setting
            );
        }
    }

    /**
     * Paramètres par défaut pour l'universitaire
     */
    private function seedUniversityDefaults(): void
    {
        $universitySettings = [
            // Limites d'âge universitaires
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'age_limits',
                'setting_key' => 'general',
                'setting_value' => [
                    'licence' => ['min' => 17, 'max' => 30],
                    'master' => ['min' => 21, 'max' => 35],
                    'doctorat' => ['min' => 24, 'max' => 45],
                ],
                'description' => 'Limites d\'âge par cycle universitaire',
                'is_required' => true,
            ],
            
            // Documents universitaires de base
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'documents',
                'setting_key' => 'basic',
                'setting_value' => [
                    'Acte de naissance',
                    'Certificat médical',
                    'Photo d\'identité',
                    'Certificat de nationalité',
                ],
                'description' => 'Documents de base pour inscription universitaire',
                'is_required' => true,
            ],
            
            // Documents spécifiques universitaires
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'documents',
                'setting_key' => 'university',
                'setting_value' => [
                    'licence' => [
                        'Diplôme de Baccalauréat',
                        'Relevé de notes du Baccalauréat',
                        'Fiche d\'orientation',
                    ],
                    'master' => [
                        'Diplôme de Licence',
                        'Relevés de notes L1, L2, L3',
                        'Lettre de motivation',
                        'CV',
                    ],
                    'doctorat' => [
                        'Diplôme de Master',
                        'Relevés de notes Master',
                        'Projet de thèse',
                        'Accord du directeur de thèse',
                    ],
                ],
                'description' => 'Documents spécifiques par cycle universitaire',
                'is_required' => true,
            ],
            
            // Frais universitaires
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'fees',
                'setting_key' => 'university',
                'setting_value' => [
                    'licence' => [
                        'inscription' => 150000,
                        'scolarite_semestrielle' => 200000,
                        'bibliotheque' => 25000,
                        'carte_etudiant' => 5000,
                        'assurance' => 15000,
                    ],
                    'master' => [
                        'inscription' => 200000,
                        'scolarite_semestrielle' => 300000,
                        'bibliotheque' => 35000,
                        'memoire' => 50000,
                    ],
                    'doctorat' => [
                        'inscription' => 250000,
                        'scolarite_annuelle' => 500000,
                        'these' => 150000,
                    ],
                ],
                'description' => 'Structure des frais universitaires en FCFA',
                'is_required' => false,
            ],
            
            // Standards LMD
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'lmd',
                'setting_key' => 'standards',
                'setting_value' => [
                    'licence' => [
                        'duree_semestres' => 6,
                        'credits_total' => 180,
                        'credits_par_semestre' => 30,
                        'ue_par_semestre' => [4, 6],
                    ],
                    'master' => [
                        'duree_semestres' => 4,
                        'credits_total' => 120,
                        'credits_par_semestre' => 30,
                        'memoire_obligatoire' => true,
                    ],
                    'doctorat' => [
                        'duree_annees' => 3,
                        'credits_total' => 180,
                        'these_obligatoire' => true,
                    ],
                ],
                'description' => 'Standards LMD officiels',
                'is_required' => true,
                'validation_rules' => [
                    'required' => true,
                ],
            ],
            
            // Seuils LMD
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'lmd',
                'setting_key' => 'thresholds',
                'setting_value' => [
                    'validation_ue' => 10.0,
                    'validation_semestre' => 10.0,
                    'validation_annee' => 10.0,
                    'compensation_autorisee' => true,
                    'note_eliminatoire' => 5.0,
                    'mention_ab' => 10.0,
                    'mention_bien' => 12.0,
                    'mention_tb' => 14.0,
                    'mention_excellence' => 16.0,
                ],
                'description' => 'Seuils et mentions système LMD',
                'is_required' => true,
                'validation_rules' => [
                    'required' => true,
                    'min' => 0,
                    'max' => 20,
                ],
            ],
            
            // Grades ECTS
            [
                'school_type' => 'university',
                'educational_level' => null,
                'setting_category' => 'ects',
                'setting_key' => 'grades',
                'setting_value' => [
                    'A' => 16.0, // Excellent
                    'B' => 14.0, // Très bien
                    'C' => 12.0, // Bien
                    'D' => 10.0, // Satisfaisant
                    'E' => 8.0,  // Passable
                    'F' => 0.0,  // Échec
                ],
                'description' => 'Grades ECTS européens',
                'is_required' => true,
                'validation_rules' => [
                    'required' => true,
                    'min' => 0,
                    'max' => 20,
                ],
            ],
        ];

        foreach ($universitySettings as $setting) {
            DefaultEducationalSetting::updateOrCreate(
                [
                    'school_type' => $setting['school_type'],
                    'educational_level' => $setting['educational_level'],
                    'setting_category' => $setting['setting_category'],
                    'setting_key' => $setting['setting_key'],
                ],
                $setting
            );
        }
    }
}