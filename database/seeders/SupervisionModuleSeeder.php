<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserLog;
use App\Models\ActivityLog;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SupervisionModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::take(10)->get();
        
        if ($users->isEmpty()) {
            $this->command->info('Aucun utilisateur trouvé. Veuillez d\'abord créer des utilisateurs.');
            return;
        }

        $this->command->info('Génération des données de test pour le Module A6...');

        // Générer des logs de connexion pour les 30 derniers jours
        foreach ($users as $user) {
            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                // Probabilité de connexion (plus haute pour les admins et enseignants)
                $probability = 0.3;
                if ($user->hasRole('admin')) $probability = 0.8;
                if ($user->isTeacher()) $probability = 0.6;
                if ($user->isStudent()) $probability = 0.4;
                
                if (rand(1, 100) <= ($probability * 100)) {
                    // Connexion
                    UserLog::create([
                        'user_id' => $user->id,
                        'action' => 'logged_in',
                        'description' => 'Utilisateur connecté',
                        'ip_address' => $this->getRandomIp(),
                        'user_agent' => $this->getRandomUserAgent(),
                        'created_at' => $date->copy()->addHours(rand(7, 22)),
                    ]);

                    // Parfois aussi une déconnexion
                    if (rand(1, 100) <= 70) {
                        UserLog::create([
                            'user_id' => $user->id,
                            'action' => 'logged_out',
                            'description' => 'Utilisateur déconnecté',
                            'ip_address' => $this->getRandomIp(),
                            'user_agent' => $this->getRandomUserAgent(),
                            'created_at' => $date->copy()->addHours(rand(8, 23)),
                        ]);
                    }
                }
            }
        }

        // Générer des logs d'activité
        foreach ($users as $user) {
            for ($i = 30; $i >= 0; $i--) {
                $date = Carbon::now()->subDays($i);
                
                // Générer 1-5 activités par jour pour les utilisateurs actifs
                $activityCount = rand(0, 5);
                if ($user->hasRole('admin')) $activityCount = rand(3, 8);
                if ($user->isTeacher()) $activityCount = rand(2, 6);
                
                for ($j = 0; $j < $activityCount; $j++) {
                    $activity = $this->getRandomActivity($user);
                    
                    ActivityLog::create([
                        'user_id' => $user->id,
                        'entity' => $activity['entity'],
                        'entity_id' => $activity['entity_id'],
                        'action' => $activity['action'],
                        'properties' => $activity['properties'],
                        'created_at' => $date->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59)),
                    ]);
                }
            }
        }

        $this->command->info('✅ Données de test générées avec succès pour le Module A6!');
        $this->command->info('📊 ' . UserLog::count() . ' logs de connexion créés');
        $this->command->info('⚡ ' . ActivityLog::count() . ' logs d\'activité créés');
    }

    private function getRandomIp()
    {
        return rand(192, 199) . '.' . rand(168, 170) . '.' . rand(1, 254) . '.' . rand(1, 254);
    }

    private function getRandomUserAgent()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Edge/120.0.0.0',
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Android 13; Mobile; rv:109.0) Gecko/109.0 Firefox/119.0',
        ];
        
        return $userAgents[array_rand($userAgents)];
    }

    private function getRandomActivity($user)
    {
        $activities = [];
        
        if ($user->isTeacher()) {
            $activities = [
                ['entity' => 'course', 'action' => 'created', 'entity_id' => rand(1, 50)],
                ['entity' => 'course', 'action' => 'updated', 'entity_id' => rand(1, 50)],
                ['entity' => 'assignment', 'action' => 'created', 'entity_id' => rand(1, 100)],
                ['entity' => 'assignment', 'action' => 'graded', 'entity_id' => rand(1, 100)],
                ['entity' => 'grade', 'action' => 'created', 'entity_id' => rand(1, 200)],
                ['entity' => 'evaluation', 'action' => 'published', 'entity_id' => rand(1, 80)],
            ];
        } elseif ($user->isStudent()) {
            $activities = [
                ['entity' => 'course', 'action' => 'viewed', 'entity_id' => rand(1, 50)],
                ['entity' => 'assignment', 'action' => 'submitted', 'entity_id' => rand(1, 100)],
                ['entity' => 'assignment', 'action' => 'viewed', 'entity_id' => rand(1, 100)],
                ['entity' => 'document', 'action' => 'downloaded', 'entity_id' => rand(1, 200)],
                ['entity' => 'course', 'action' => 'completed', 'entity_id' => rand(1, 50)],
            ];
        } else {
            $activities = [
                ['entity' => 'student', 'action' => 'created', 'entity_id' => rand(1, 500)],
                ['entity' => 'student', 'action' => 'updated', 'entity_id' => rand(1, 500)],
                ['entity' => 'teacher', 'action' => 'created', 'entity_id' => rand(1, 100)],
                ['entity' => 'payment', 'action' => 'confirmed', 'entity_id' => rand(1, 1000)],
                ['entity' => 'school_fee', 'action' => 'created', 'entity_id' => rand(1, 50)],
                ['entity' => 'class', 'action' => 'created', 'entity_id' => rand(1, 30)],
            ];
        }
        
        $activity = $activities[array_rand($activities)];
        
        // Ajouter des propriétés selon le type d'activité
        $properties = [];
        switch ($activity['action']) {
            case 'created':
                $properties = ['status' => 'active', 'created_by' => $user->name];
                break;
            case 'updated':
                $properties = ['updated_fields' => ['name', 'description'], 'updated_by' => $user->name];
                break;
            case 'graded':
                $properties = ['grade' => rand(8, 20), 'max_grade' => 20];
                break;
            case 'submitted':
                $properties = ['submission_type' => 'file', 'file_count' => rand(1, 3)];
                break;
            case 'viewed':
                $properties = ['duration' => rand(30, 1800)]; // secondes
                break;
            case 'downloaded':
                $properties = ['file_size' => rand(100, 5000) . 'KB'];
                break;
        }
        
        $activity['properties'] = $properties;
        
        return $activity;
    }
}