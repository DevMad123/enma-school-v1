<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DefaultEducationalSetting;

class DefaultEducationalSettingsSeederSimple extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des paramètres éducatifs par défaut...');

        $settings = [
            // Paramètres préuniversitaires
            [
                'school_type' => 'preuniversity',
                'edu_level' => null,
                'category' => 'age_limits',
                'key' => 'general',
                'value' => [
                    'primaire' => ['min' => 6, 'max' => 12],
                    'college' => ['min' => 12, 'max' => 16],
                    'lycee' => ['min' => 15, 'max' => 20],
                ],
                'is_required' => true,
                'description' => 'Limites d\'âge par niveau préuniversitaire',
            ],
            [
                'school_type' => 'preuniversity',
                'edu_level' => null,
                'category' => 'evaluation',
                'key' => 'thresholds',
                'value' => [
                    'excellent' => 16.0,
                    'tres_bien' => 14.0,
                    'bien' => 12.0,
                    'assez_bien' => 10.0,
                    'passable' => 8.0,
                    'echec' => 0.0,
                ],
                'is_required' => true,
                'description' => 'Seuils d\'évaluation préuniversitaire',
            ],
            
            // Paramètres universitaires
            [
                'school_type' => 'university',
                'edu_level' => null,
                'category' => 'age_limits',
                'key' => 'general',
                'value' => [
                    'licence' => ['min' => 17, 'max' => 30],
                    'master' => ['min' => 21, 'max' => 35],
                    'doctorat' => ['min' => 24, 'max' => 45],
                ],
                'is_required' => true,
                'description' => 'Limites d\'âge universitaire',
            ],
            [
                'school_type' => 'university',
                'edu_level' => null,
                'category' => 'lmd',
                'key' => 'standards',
                'value' => [
                    'licence' => [
                        'credits_total' => 180,
                        'duree_semestres' => 6,
                        'moyenne_passage' => 10.0,
                    ],
                    'master' => [
                        'credits_total' => 120,
                        'duree_semestres' => 4,
                        'moyenne_passage' => 10.0,
                    ],
                    'doctorat' => [
                        'credits_total' => 180,
                        'duree_annees' => 3,
                        'moyenne_passage' => 12.0,
                    ],
                ],
                'is_required' => true,
                'description' => 'Standards LMD universitaires',
            ],
            [
                'school_type' => 'university',
                'edu_level' => null,
                'category' => 'evaluation',
                'key' => 'thresholds',
                'value' => [
                    'excellent' => 16.0,
                    'tres_bien' => 14.0,
                    'bien' => 12.0,
                    'assez_bien' => 10.0,
                    'passable' => 8.0,
                    'echec' => 0.0,
                ],
                'is_required' => true,
                'description' => 'Seuils d\'évaluation universitaire',
            ],
        ];

        foreach ($settings as $setting) {
            DefaultEducationalSetting::updateOrCreate(
                [
                    'school_type' => $setting['school_type'],
                    'edu_level' => $setting['edu_level'],
                    'category' => $setting['category'],
                    'key' => $setting['key'],
                ],
                $setting
            );
        }

        $this->command->info('Paramètres éducatifs par défaut créés avec succès.');
    }
}