<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseUnit;
use App\Models\CourseUnitElement;
use App\Models\School;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;

class CourseUnitElementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');
        
        Log::info('Début du seeding des ECUE...');
        
        // Récupérer toutes les UE existantes
        $courseUnits = CourseUnit::where('is_active', true)->get();
        
        if ($courseUnits->isEmpty()) {
            $this->command->error('Aucune UE trouvée. Veuillez d\'abord créer des UE avec leurs seeders.');
            return;
        }

        $this->command->info("Création d'ECUE pour {$courseUnits->count()} UE...");

        foreach ($courseUnits as $courseUnit) {
            $this->createEcueForCourseUnit($courseUnit, $faker);
        }

        Log::info('Seeding des ECUE terminé avec succès');
        $this->command->info('✅ ECUE créés avec succès !');
    }

    /**
     * Créer des ECUE pour une UE donnée en respectant les contraintes LMD
     */
    private function createEcueForCourseUnit(CourseUnit $courseUnit, $faker): void
    {
        // Déterminer le nombre d'ECUE selon le type et les crédits de l'UE
        $elementsCount = $this->determineElementsCount($courseUnit);
        
        if ($elementsCount === 0) {
            return; // Pas d'ECUE pour cette UE
        }

        $this->command->line("  - {$courseUnit->code}: {$elementsCount} ECUE");

        // Calculer la répartition des crédits et heures
        $creditsDistribution = $this->distributeCredits($courseUnit->credits, $elementsCount);
        $hoursDistribution = $this->distributeHours($courseUnit, $elementsCount);

        // Types d'ECUE possibles avec leurs caractéristiques
        $ecueTypes = $this->getEcueTypesForCourseUnit($courseUnit);

        for ($i = 0; $i < $elementsCount; $i++) {
            $ecueType = $ecueTypes[$i % count($ecueTypes)];
            
            $credits = $creditsDistribution[$i];
            $hours = $hoursDistribution[$i];
            
            // Générer les données de l'ECUE
            $ecueData = $this->generateEcueData($courseUnit, $ecueType, $credits, $hours, $i + 1, $faker);
            
            try {
                CourseUnitElement::create($ecueData);
                $this->command->line("    ✓ {$ecueData['code']} - {$ecueData['name']}");
            } catch (\Exception $e) {
                $this->command->error("    ✗ Erreur création ECUE {$ecueData['code']}: " . $e->getMessage());
                Log::error("Erreur création ECUE {$ecueData['code']}", ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Déterminer le nombre optimal d'ECUE pour une UE
     */
    private function determineElementsCount(CourseUnit $courseUnit): int
    {
        $credits = $courseUnit->credits;
        $type = $courseUnit->type ?? 'obligatoire';
        
        // Règles LMD pour le nombre d'ECUE
        if ($credits <= 1) {
            return 1; // Une seule ECUE pour les petites UE
        } elseif ($credits <= 3) {
            return rand(1, 2); // 1-2 ECUE pour les UE moyennes
        } elseif ($credits <= 6) {
            return rand(2, 3); // 2-3 ECUE pour les UE importantes
        } else {
            return rand(3, 4); // 3-4 ECUE pour les très grosses UE
        }
    }

    /**
     * Distribuer les crédits entre les ECUE
     */
    private function distributeCredits(float $totalCredits, int $elementsCount): array
    {
        if ($elementsCount === 1) {
            return [$totalCredits];
        }

        $distribution = [];
        $remainingCredits = $totalCredits;
        
        // Distribuer les crédits avec minimum 0.5 par ECUE
        for ($i = 0; $i < $elementsCount - 1; $i++) {
            $minCredits = 0.5;
            $maxCredits = min(3, $remainingCredits - (($elementsCount - $i - 1) * 0.5));
            
            $credits = $this->roundToHalf(rand($minCredits * 2, $maxCredits * 2) / 2);
            $distribution[] = $credits;
            $remainingCredits -= $credits;
        }
        
        // Attribuer le reste au dernier ECUE
        $distribution[] = $this->roundToHalf($remainingCredits);
        
        return $distribution;
    }

    /**
     * Distribuer les heures entre les ECUE
     */
    private function distributeHours(CourseUnit $courseUnit, int $elementsCount): array
    {
        $totalHours = max($courseUnit->hours_total, $courseUnit->hours_cm + $courseUnit->hours_td + $courseUnit->hours_tp);
        
        if ($elementsCount === 1) {
            return [[$courseUnit->hours_cm, $courseUnit->hours_td, $courseUnit->hours_tp]];
        }

        $distribution = [];
        $remainingCm = $courseUnit->hours_cm ?? 0;
        $remainingTd = $courseUnit->hours_td ?? 0;
        $remainingTp = $courseUnit->hours_tp ?? 0;
        
        for ($i = 0; $i < $elementsCount; $i++) {
            $isLast = ($i === $elementsCount - 1);
            
            if ($isLast) {
                // Dernier ECUE : utiliser le reste
                $distribution[] = [$remainingCm, $remainingTd, $remainingTp];
            } else {
                // Distribuer proportionnellement avec variation
                $cmPart = $remainingCm > 0 ? rand(0, ceil($remainingCm / ($elementsCount - $i))) : 0;
                $tdPart = $remainingTd > 0 ? rand(0, ceil($remainingTd / ($elementsCount - $i))) : 0;
                $tpPart = $remainingTp > 0 ? rand(0, ceil($remainingTp / ($elementsCount - $i))) : 0;
                
                $distribution[] = [$cmPart, $tdPart, $tpPart];
                
                $remainingCm -= $cmPart;
                $remainingTd -= $tdPart;
                $remainingTp -= $tpPart;
            }
        }
        
        return $distribution;
    }

    /**
     * Obtenir les types d'ECUE possibles pour une UE
     */
    private function getEcueTypesForCourseUnit(CourseUnit $courseUnit): array
    {
        $baseTypes = [
            [
                'prefix' => 'CM',
                'name_patterns' => ['Cours magistral', 'Théorie'],
                'evaluation_types' => ['examen_final', 'mixte'],
                'favor_cm' => true
            ],
            [
                'prefix' => 'TD',
                'name_patterns' => ['Travaux dirigés', 'Exercices', 'Applications'],
                'evaluation_types' => ['controle_continu', 'mixte'],
                'favor_td' => true
            ],
            [
                'prefix' => 'TP',
                'name_patterns' => ['Travaux pratiques', 'Laboratoire', 'Pratique'],
                'evaluation_types' => ['controle_continu', 'controle_continu'],
                'favor_tp' => true
            ],
            [
                'prefix' => 'PRJ',
                'name_patterns' => ['Projet', 'Mini-projet', 'Étude de cas'],
                'evaluation_types' => ['mixte'],
                'favor_mixed' => true
            ]
        ];

        // Adapter selon la nature de l'UE
        $selectedTypes = [];
        $courseType = strtolower($courseUnit->type ?? '');
        $courseName = strtolower($courseUnit->name);
        
        // Logique de sélection selon le nom/type de l'UE
        if (str_contains($courseName, 'pratique') || str_contains($courseName, 'laboratoire')) {
            $selectedTypes = [$baseTypes[2], $baseTypes[1]]; // TP + TD
        } elseif (str_contains($courseName, 'projet')) {
            $selectedTypes = [$baseTypes[3], $baseTypes[1]]; // Projet + TD
        } elseif (str_contains($courseName, 'théorie') || str_contains($courseName, 'fondamentaux')) {
            $selectedTypes = [$baseTypes[0], $baseTypes[1]]; // CM + TD
        } else {
            // Sélection équilibrée par défaut
            $selectedTypes = [$baseTypes[0], $baseTypes[1], $baseTypes[2]];
        }

        return $selectedTypes;
    }

    /**
     * Générer les données pour un ECUE
     */
    private function generateEcueData(CourseUnit $courseUnit, array $ecueType, float $credits, array $hours, int $number, $faker): array
    {
        [$hoursCm, $hoursTd, $hoursTp] = $hours;
        $hoursTotal = $hoursCm + $hoursTd + $hoursTp;
        
        // Générer le code ECUE
        $code = $courseUnit->code . '-' . $ecueType['prefix'] . str_pad($number, 2, '0', STR_PAD_LEFT);
        
        // Choisir un nom approprié
        $namePattern = $faker->randomElement($ecueType['name_patterns']);
        $subjectVariation = $this->getSubjectVariation($courseUnit->name, $namePattern, $faker);
        $name = $namePattern . ' - ' . $subjectVariation;
        
        // Choisir le type d'évaluation
        $evaluationType = $faker->randomElement($ecueType['evaluation_types']);
        
        // Calculer le coefficient (proportionnel aux crédits avec variation)
        $coefficient = max(0.5, min(3, $credits + rand(-1, 1) * 0.5));
        
        // Générer une description
        $description = $this->generateEcueDescription($namePattern, $subjectVariation, $evaluationType, $faker);
        
        return [
            'course_unit_id' => $courseUnit->id,
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'credits' => $this->roundToHalf($credits),
            'hours_cm' => $hoursCm,
            'hours_td' => $hoursTd,
            'hours_tp' => $hoursTp,
            'hours_total' => $hoursTotal,
            'evaluation_type' => $evaluationType,
            'coefficient' => $this->roundToHalf($coefficient),
            'status' => $faker->randomElement(['active', 'active', 'active', 'inactive']), // 75% actif
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Générer une variation thématique pour l'ECUE
     */
    private function getSubjectVariation(string $courseUnitName, string $namePattern, $faker): string
    {
        $courseWords = explode(' ', strtolower($courseUnitName));
        $mainSubject = $courseWords[0] ?? 'Général';
        
        $variations = match(strtolower($namePattern)) {
            'cours magistral', 'théorie' => [
                'Fondamentaux',
                'Principes de base',
                'Concepts avancés',
                'Théorie générale'
            ],
            'travaux dirigés', 'exercices', 'applications' => [
                'Exercices d\'application',
                'Résolution de problèmes',
                'Cas pratiques',
                'Méthodologie'
            ],
            'travaux pratiques', 'laboratoire', 'pratique' => [
                'Mise en pratique',
                'Expérimentation',
                'Manipulation',
                'Atelier pratique'
            ],
            'projet', 'mini-projet', 'étude de cas' => [
                'Projet d\'application',
                'Étude de cas',
                'Mini-projet',
                'Réalisation pratique'
            ],
            default => ['Application', 'Développement', 'Étude']
        };
        
        return ucfirst($mainSubject) . ' - ' . $faker->randomElement($variations);
    }

    /**
     * Générer une description pour l'ECUE
     */
    private function generateEcueDescription(string $namePattern, string $subjectVariation, string $evaluationType, $faker): string
    {
        $descriptions = [
            'cours magistral' => "Enseignement théorique couvrant les concepts fondamentaux de {$subjectVariation}. Approche méthodologique et présentation des principes essentiels.",
            'travaux dirigés' => "Séances d'exercices et de résolution de problèmes sur {$subjectVariation}. Accompagnement méthodologique et application des concepts théoriques.",
            'travaux pratiques' => "Mise en pratique des concepts à travers des manipulations et expérimentations de {$subjectVariation}. Apprentissage par la pratique.",
            'projet' => "Réalisation d'un projet complet sur {$subjectVariation}. Développement de l'autonomie et application intégrée des compétences.",
        ];
        
        $baseDescription = $descriptions[strtolower($namePattern)] ?? "Élément constitutif portant sur {$subjectVariation}.";
        
        // Ajouter des informations sur l'évaluation
        $evaluationInfo = match($evaluationType) {
            'controle_continu' => " Évaluation en contrôle continu.",
            'examen_final' => " Évaluation par examen final.",
            'mixte' => " Évaluation mixte (contrôle continu + examen).",
            default => ""
        };
        
        return $baseDescription . $evaluationInfo;
    }

    /**
     * Arrondir à la demi-unité la plus proche (0.5, 1, 1.5, 2, etc.)
     */
    private function roundToHalf(float $value): float
    {
        return round($value * 2) / 2;
    }
}
