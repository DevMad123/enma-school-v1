<?php

namespace App\Services\Settings;

use App\Repositories\EducationalSettingsRepository;
use App\Models\School;

class PreUniversitySettingsService extends EducationalSettingsService
{
    protected string $schoolType = 'preuniversity';

    /**
     * Limites d'âge par niveau préuniversitaire
     */
    public function getAgeLimits(): array
    {
        return $this->getSetting('age_limits.general', [
            'prescolaire' => ['min' => 3, 'max' => 6],
            'primaire' => ['min' => 6, 'max' => 12],
            'college' => ['min' => 12, 'max' => 16],
            'lycee' => ['min' => 16, 'max' => 20],
        ]);
    }

    /**
     * Documents requis pour inscription préuniversitaire
     */
    public function getRequiredDocuments(): array
    {
        $basicDocs = $this->getSetting('documents.basic', [
            'Acte de naissance',
            'Certificat médical',
            'Photo d\'identité',
            'Bulletin de l\'année précédente',
        ]);

        $preunivDocs = $this->getSetting('documents.preuniversity', [
            'prescolaire' => ['Carnet de vaccinations'],
            'primaire' => ['Certificat de fin de préscolaire'],
            'college' => ['Certificat d\'études primaires (CEP)'],
            'lycee' => ['Diplôme BEPC ou équivalent'],
        ]);

        return array_merge(['general' => $basicDocs], $preunivDocs);
    }

    /**
     * Seuils d'évaluation préuniversitaire (système ivoirien)
     */
    public function getEvaluationThresholds(): array
    {
        return $this->getSetting('evaluation.thresholds', [
            'excellent' => 16.0,
            'tres_bien' => 14.0,
            'bien' => 12.0,
            'assez_bien' => 10.0,
            'passable' => 8.0,
            'echec' => 0.0,
        ]);
    }

    /**
     * Structure des frais scolaires préuniversitaires
     */
    public function getFeeStructure(): array
    {
        return $this->getSetting('fees.structure', [
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
        ]);
    }

    /**
     * Paramètres spécifiques aux bulletins préuniversitaires
     */
    public function getBulletinSettings(): array
    {
        return $this->getSetting('bulletin.settings', [
            'format' => 'A4',
            'logo_school' => true,
            'show_absences' => true,
            'show_conduites' => true,
            'show_rang' => true,
            'show_appreciation_generale' => true,
            'signature_required' => ['directeur', 'professeur_principal'],
        ]);
    }

    /**
     * Paramètres des coefficients par matière et niveau
     */
    public function getSubjectCoefficients(): array
    {
        return $this->getSetting('subjects.coefficients', [
            'primaire' => [
                'francais' => 3,
                'mathematiques' => 3,
                'histoire_geo' => 2,
                'sciences' => 2,
                'eduction_civique' => 1,
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
            'lycee' => [
                // Variables selon la série
                'serie_a' => [
                    'francais' => 5,
                    'philosophie' => 4,
                    'histoire_geo' => 4,
                    'anglais' => 3,
                    'mathematiques' => 2,
                ],
                'serie_c' => [
                    'mathematiques' => 5,
                    'sciences_physiques' => 4,
                    'svt' => 3,
                    'francais' => 3,
                    'anglais' => 2,
                ],
                'serie_d' => [
                    'mathematiques' => 4,
                    'sciences_physiques' => 4,
                    'svt' => 4,
                    'francais' => 3,
                    'anglais' => 2,
                ],
            ],
        ]);
    }

    /**
     * Règles de passage de classe
     */
    public function getPromotionRules(): array
    {
        return $this->getSetting('promotion.rules', [
            'moyenne_generale_requise' => 10.0,
            'moyenne_francais_maths_requise' => 8.0,
            'nombre_matieres_eliminatoires_max' => 3,
            'note_eliminatoire_seuil' => 5.0,
            'possibilite_rachat' => [
                'enabled' => true,
                'deficit_max' => 2.0,
                'matieres_concernees' => ['francais', 'mathematiques'],
            ],
            'redoublement_max_consecutif' => 2,
        ]);
    }

    /**
     * Configuration des périodes d'évaluation
     */
    public function getEvaluationPeriods(): array
    {
        return $this->getSetting('evaluation.periods', [
            'trimestres' => [
                'trimestre_1' => [
                    'debut' => '09-01',
                    'fin' => '12-15',
                    'coefficient' => 1,
                ],
                'trimestre_2' => [
                    'debut' => '01-05',
                    'fin' => '04-15',
                    'coefficient' => 1,
                ],
                'trimestre_3' => [
                    'debut' => '04-20',
                    'fin' => '07-15',
                    'coefficient' => 1,
                ],
            ],
            'composition_coefficient' => 2,
            'controle_coefficient' => 1,
        ]);
    }
}