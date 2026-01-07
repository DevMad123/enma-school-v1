<?php

namespace App\Services\Settings;

use App\Repositories\EducationalSettingsRepository;
use App\Models\School;

class UniversitySettingsService extends EducationalSettingsService
{
    protected string $schoolType = 'university';

    /**
     * Limites d'âge universitaires (plus flexibles)
     */
    public function getAgeLimits(): array
    {
        return $this->getSetting('age_limits.general', [
            'licence' => ['min' => 17, 'max' => 30],
            'master' => ['min' => 21, 'max' => 35],
            'doctorat' => ['min' => 24, 'max' => 45],
        ]);
    }

    /**
     * Documents requis pour inscription universitaire
     */
    public function getRequiredDocuments(): array
    {
        $basicDocs = $this->getSetting('documents.basic', [
            'Acte de naissance',
            'Certificat médical',
            'Photo d\'identité',
            'Certificat de nationalité',
        ]);

        $universityDocs = $this->getSetting('documents.university', [
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
                'Dossier de candidature complet',
            ],
        ]);

        return array_merge(['general' => $basicDocs], $universityDocs);
    }

    /**
     * Frais universitaires par cycle LMD
     */
    public function getFeeStructure(): array
    {
        return $this->getSetting('fees.university', [
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
                'carte_etudiant' => 5000,
                'assurance' => 20000,
                'memoire' => 50000,
            ],
            'doctorat' => [
                'inscription' => 250000,
                'scolarite_annuelle' => 500000,
                'bibliotheque' => 50000,
                'carte_etudiant' => 5000,
                'assurance' => 25000,
                'these' => 150000,
            ],
        ]);
    }

    /**
     * Standards LMD officiels
     */
    public function getLMDStandards(): array
    {
        return $this->getSetting('lmd.standards', [
            'licence' => [
                'duree_semestres' => 6,
                'credits_total' => 180,
                'credits_par_semestre' => 30,
                'ue_par_semestre' => [4, 6],
                'credits_par_ue' => [3, 9],
            ],
            'master' => [
                'duree_semestres' => 4,
                'credits_total' => 120,
                'credits_par_semestre' => 30,
                'ue_par_semestre' => [4, 6],
                'credits_par_ue' => [3, 9],
                'memoire_obligatoire' => true,
                'credits_memoire' => 30,
            ],
            'doctorat' => [
                'duree_annees' => 3,
                'credits_total' => 180,
                'these_obligatoire' => true,
                'publications_min' => 2,
                'seminaires_obligatoires' => true,
            ],
        ]);
    }

    /**
     * Seuils et grades LMD/ECTS
     */
    public function getEvaluationThresholds(): array
    {
        $lmdThresholds = $this->getSetting('lmd.thresholds', [
            'validation_ue' => 10.0,
            'validation_semestre' => 10.0,
            'validation_annee' => 10.0,
            'compensation_autorisee' => true,
            'note_eliminatoire' => 5.0,
            'mention_ab' => 10.0,
            'mention_bien' => 12.0,
            'mention_tb' => 14.0,
            'mention_excellence' => 16.0,
        ]);

        $ectsGrades = $this->getSetting('ects.grades', [
            'A' => 16.0, // Excellent
            'B' => 14.0, // Très bien
            'C' => 12.0, // Bien
            'D' => 10.0, // Satisfaisant
            'E' => 8.0,  // Passable
            'F' => 0.0,  // Échec
        ]);

        return array_merge($lmdThresholds, ['ects_grades' => $ectsGrades]);
    }

    /**
     * Règles de validation LMD
     */
    public function getLMDValidationRules(): array
    {
        return $this->getSetting('lmd.validation', [
            'compensation_ue' => [
                'enabled' => true,
                'seuil_min' => 8.0,
                'moyenne_generale_min' => 10.0,
            ],
            'compensation_semestre' => [
                'enabled' => true,
                'deficit_max' => 2.0,
                'ue_validees_min_pct' => 60,
            ],
            'rattrapage' => [
                'enabled' => true,
                'notes_min' => 5.0,
                'sessions_max' => 2,
                'delai_entre_sessions' => 15, // jours
            ],
            'redoublement' => [
                'autorise' => true,
                'max_consecutif' => 2,
                'credits_min_acquis' => 24, // sur 60 pour l'année
            ],
        ]);
    }

    /**
     * Configuration des mentions et bourses
     */
    public function getHonorsAndScholarships(): array
    {
        return $this->getSetting('honors.scholarships', [
            'mentions' => [
                'mention_excellence' => [
                    'seuil' => 16.0,
                    'avantages' => ['bourse_excellence', 'inscription_gratuite_master'],
                ],
                'mention_tres_bien' => [
                    'seuil' => 14.0,
                    'avantages' => ['reduction_frais_50pct'],
                ],
                'mention_bien' => [
                    'seuil' => 12.0,
                    'avantages' => ['reduction_frais_25pct'],
                ],
            ],
            'bourses' => [
                'sociale' => [
                    'criteres' => ['revenus_parents_max' => 500000],
                    'montant_mensuel' => 50000,
                ],
                'merite' => [
                    'criteres' => ['moyenne_min' => 15.0],
                    'montant_mensuel' => 75000,
                ],
                'excellence' => [
                    'criteres' => ['moyenne_min' => 17.0, 'rang_max' => 3],
                    'montant_mensuel' => 100000,
                ],
            ],
        ]);
    }

    /**
     * Paramètres des jurys et délibérations
     */
    public function getDeliberationSettings(): array
    {
        return $this->getSetting('deliberation.settings', [
            'jury_composition' => [
                'president' => 'required',
                'membres_min' => 3,
                'externes_min' => 1,
                'rapporteur' => 'required',
            ],
            'frequence' => [
                'semestre' => 'fin_semestre',
                'annee' => 'fin_annee_academique',
            ],
            'documents_requis' => [
                'proces_verbal',
                'releve_notes',
                'decision_jury',
                'statistiques_resultats',
            ],
            'delais' => [
                'convocation_membres' => 7, // jours
                'publication_resultats' => 3, // jours après délibération
                'recours_possible' => 15, // jours
            ],
        ]);
    }

    /**
     * Configuration des stages et mémoires
     */
    public function getInternshipAndThesisSettings(): array
    {
        return $this->getSetting('internship.thesis', [
            'stages' => [
                'licence_l3' => [
                    'obligatoire' => true,
                    'duree_min_semaines' => 8,
                    'credits' => 6,
                    'rapport_requis' => true,
                ],
                'master_m1' => [
                    'obligatoire' => false,
                    'duree_min_semaines' => 12,
                    'credits' => 12,
                ],
                'master_m2' => [
                    'obligatoire' => true,
                    'duree_min_semaines' => 16,
                    'credits' => 30,
                ],
            ],
            'memoires' => [
                'licence' => [
                    'requis' => false,
                    'pages_min' => 30,
                    'credits' => 9,
                ],
                'master' => [
                    'requis' => true,
                    'pages_min' => 80,
                    'credits' => 30,
                    'soutenance_orale' => true,
                ],
            ],
            'theses' => [
                'doctorat' => [
                    'duree_max_annees' => 6,
                    'comite_suivi_requis' => true,
                    'rapports_annuels' => true,
                    'publications_min' => 2,
                ],
            ],
        ]);
    }
}