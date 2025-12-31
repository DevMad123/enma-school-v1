<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PedagogicalSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = \App\Models\School::getActiveSchool();
        
        if (!$school) {
            $this->command->info('Aucune école active trouvée. Veuillez d\'abord créer une école.');
            return;
        }

        // Paramètres pédagogiques par défaut selon le type d'établissement
        $isUniversity = $school->type === 'university';
        $gradingScale = $school->grading_system;
        
        $defaultSettings = [
            'validation_threshold' => $isUniversity ? '50' : '10',
            'redoublement_threshold' => $isUniversity ? '40' : '8',
            'bulletin_footer_text' => 'Ce document est généré automatiquement par le système de gestion scolaire.',
            'automatic_promotion' => 'false',
            'mention_system' => 'true',
            'validation_subjects_required' => '80',
        ];

        // Ajuster selon le système de notation
        if ($gradingScale === '100') {
            $defaultSettings['validation_threshold'] = '50';
            $defaultSettings['redoublement_threshold'] = '40';
        } elseif ($gradingScale === '20') {
            $defaultSettings['validation_threshold'] = '10';
            $defaultSettings['redoublement_threshold'] = '8';
        }

        foreach ($defaultSettings as $key => $value) {
            // Ne pas écraser les paramètres existants
            if (!$school->settings()->where('key', $key)->exists()) {
                $school->setSetting($key, $value);
                $this->command->info("Paramètre '{$key}' défini à '{$value}'");
            } else {
                $this->command->info("Paramètre '{$key}' déjà configuré");
            }
        }

        $this->command->info('Paramètres pédagogiques par défaut configurés avec succès !');
    }
}
