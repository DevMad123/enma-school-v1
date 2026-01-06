<?php

namespace App\Console\Commands;

use App\Models\School;
use App\Models\UFR;
use App\Models\Department;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateUniversityTestData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'university:create-test-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Créer des données de test pour le système universitaire';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🎓 Création des données de test universitaires...');
        
        DB::beginTransaction();
        
        try {
            // 1. Vérifier/Créer une école universitaire
            $universitySchool = School::where('type', 'university')->first();
            
            if (!$universitySchool) {
                $universitySchool = School::create([
                    'name' => 'Université de Test',
                    'type' => 'university',
                    'address' => '123 Avenue de l\'Université, Abidjan',
                    'phone' => '+225 22 44 55 66',
                    'email' => 'contact@universite-test.ci',
                    'website' => 'https://universite-test.ci',
                    'is_active' => true,
                    'settings' => json_encode([])
                ]);
                $this->info("✅ École universitaire créée: {$universitySchool->name}");
            } else {
                $this->info("ℹ️ École universitaire existante: {$universitySchool->name}");
            }

            // 2. Créer les UFR
            $ufrs = [];
            $ufrData = [
                [
                    'name' => 'UFR Sciences et Technologies',
                    'code' => 'ST',
                    'short_name' => 'Sciences',
                    'address' => '456 Avenue des Sciences, Campus Nord'
                ],
                [
                    'name' => 'UFR Sciences Économiques et Gestion',
                    'code' => 'SEG',
                    'short_name' => 'Économie',
                    'address' => '789 Boulevard de l\'Économie, Campus Sud'
                ],
                [
                    'name' => 'UFR Lettres et Sciences Humaines',
                    'code' => 'LSH',
                    'short_name' => 'Lettres',
                    'address' => '321 Rue des Humanités, Campus Central'
                ]
            ];

            foreach ($ufrData as $ufrInfo) {
                $ufr = UFR::where('school_id', $universitySchool->id)
                         ->where('code', $ufrInfo['code'])
                         ->first();
                
                if (!$ufr) {
                    $ufr = UFR::create([
                        'school_id' => $universitySchool->id,
                        'name' => $ufrInfo['name'],
                        'code' => $ufrInfo['code'],
                        'short_name' => $ufrInfo['short_name'],
                        'address' => $ufrInfo['address'],
                        'is_active' => true
                    ]);
                    $this->info("✅ UFR créée: {$ufr->name}");
                } else {
                    $this->info("ℹ️ UFR existante: {$ufr->name}");
                }
                
                $ufrs[] = $ufr;
            }

            // 3. Créer les départements
            $departments = [];
            $departmentData = [
                // UFR Sciences
                ['ufr_code' => 'ST', 'name' => 'Département d\'Informatique', 'code' => 'DEPT-INFO', 'short_name' => 'INFO'],
                ['ufr_code' => 'ST', 'name' => 'Département de Mathématiques', 'code' => 'DEPT-MATH', 'short_name' => 'MATH'],
                ['ufr_code' => 'ST', 'name' => 'Département de Physique', 'code' => 'DEPT-PHYS', 'short_name' => 'PHYS'],
                
                // UFR Économie
                ['ufr_code' => 'SEG', 'name' => 'Département d\'Économie', 'code' => 'DEPT-ECON', 'short_name' => 'ECON'],
                ['ufr_code' => 'SEG', 'name' => 'Département de Gestion', 'code' => 'DEPT-GEST', 'short_name' => 'GEST'],
                
                // UFR Lettres
                ['ufr_code' => 'LSH', 'name' => 'Département de Français', 'code' => 'DEPT-FRAN', 'short_name' => 'FRAN'],
                ['ufr_code' => 'LSH', 'name' => 'Département d\'Histoire', 'code' => 'DEPT-HIST', 'short_name' => 'HIST'],
            ];

            foreach ($departmentData as $deptInfo) {
                $ufr = collect($ufrs)->firstWhere('code', $deptInfo['ufr_code']);
                
                $department = Department::where('school_id', $universitySchool->id)
                                      ->where('ufr_id', $ufr->id)
                                      ->where('code', $deptInfo['code'])
                                      ->first();
                
                if (!$department) {
                    $department = Department::create([
                        'school_id' => $universitySchool->id,
                        'ufr_id' => $ufr->id,
                        'name' => $deptInfo['name'],
                        'code' => $deptInfo['code'],
                        'short_name' => $deptInfo['short_name'],
                        'is_active' => true
                    ]);
                    $this->info("✅ Département créé: {$department->name}");
                } else {
                    $this->info("ℹ️ Département existant: {$department->name}");
                }
                
                $departments[] = $department;
            }

            // 4. Créer des enseignants universitaires
            $teachersData = [
                [
                    'first_name' => 'Marie-Claire',
                    'last_name' => 'KOUADIO',
                    'email' => 'marie.kouadio@universite-test.ci',
                    'phone' => '+225 07 12 34 56',
                    'academic_rank' => 'professeur',
                    'specialization' => 'Mathématiques Appliquées',
                    'research_interests' => 'Analyse numérique, Optimisation, Modélisation mathématique',
                    'office_location' => 'Bureau 301, Bâtiment Sciences',
                    'salary' => 850000,
                    'employee_id' => 'UNI-2024-001',
                    'department_code' => 'DEPT-MATH',
                    'qualifications' => "• Doctorat en Mathématiques - Université Paris-Saclay (2010)\n• HDR en Mathématiques Appliquées (2018)"
                ],
                [
                    'first_name' => 'Jean-Baptiste',
                    'last_name' => 'KONE',
                    'email' => 'jean.kone@universite-test.ci',
                    'phone' => '+225 05 87 65 43',
                    'academic_rank' => 'maitre_de_conferences',
                    'specialization' => 'Génie Informatique',
                    'research_interests' => 'Intelligence artificielle, Machine learning, Traitement de données',
                    'office_location' => 'Bureau 205, Bâtiment Informatique',
                    'salary' => 650000,
                    'employee_id' => 'UNI-2024-002',
                    'department_code' => 'DEPT-INFO',
                    'qualifications' => "• Doctorat en Informatique - INRIA Sophia Antipolis (2015)\n• Master en Intelligence Artificielle"
                ],
                [
                    'first_name' => 'Aminata',
                    'last_name' => 'DIALLO',
                    'email' => 'aminata.diallo@universite-test.ci',
                    'phone' => '+225 07 98 76 54',
                    'academic_rank' => 'maitre_assistant',
                    'specialization' => 'Économie du Développement',
                    'research_interests' => 'Économie rurale, Microfinance, Politiques publiques en Afrique',
                    'office_location' => 'Bureau 102, Bâtiment Sciences Économiques',
                    'salary' => 520000,
                    'employee_id' => 'UNI-2024-003',
                    'department_code' => 'DEPT-ECON',
                    'qualifications' => "• Doctorat en Sciences Économiques - Université Cheikh Anta Diop (2018)"
                ],
                [
                    'first_name' => 'Désiré',
                    'last_name' => 'YAPI',
                    'email' => 'desire.yapi@universite-test.ci',
                    'phone' => '+225 05 44 33 22',
                    'academic_rank' => 'assistant',
                    'specialization' => 'Physique Théorique',
                    'research_interests' => 'Physique quantique, Matériaux nano-structurés',
                    'office_location' => 'Bureau 404, Bâtiment Physique',
                    'salary' => 450000,
                    'employee_id' => 'UNI-2024-004',
                    'department_code' => 'DEPT-PHYS',
                    'qualifications' => "• Doctorat en Physique - École Polytechnique (2020)"
                ],
                [
                    'first_name' => 'Fatou',
                    'last_name' => 'TRAORE',
                    'email' => 'fatou.traore@universite-test.ci',
                    'phone' => '+225 07 55 66 77',
                    'academic_rank' => 'professeur_titulaire',
                    'specialization' => 'Littérature Africaine',
                    'research_interests' => 'Littérature orale africaine, Sociolinguistique, Patrimoine culturel',
                    'office_location' => 'Bureau 201, Bâtiment Lettres',
                    'salary' => 950000,
                    'employee_id' => 'UNI-2024-005',
                    'department_code' => 'DEPT-FRAN',
                    'qualifications' => "• Doctorat d'État en Littérature Comparée - Université de la Sorbonne (2005)"
                ]
            ];

            foreach ($teachersData as $teacherData) {
                // Vérifier si l'utilisateur existe
                $existingUser = User::where('email', $teacherData['email'])->first();
                
                if ($existingUser) {
                    $this->info("ℹ️ Enseignant existant: {$teacherData['first_name']} {$teacherData['last_name']}");
                    continue;
                }

                // Trouver le département
                $department = collect($departments)->firstWhere('code', $teacherData['department_code']);
                
                if (!$department) {
                    $this->error("❌ Département non trouvé pour {$teacherData['department_code']}");
                    continue;
                }

                // Créer l'utilisateur
                $user = User::create([
                    'name' => $teacherData['first_name'] . ' ' . $teacherData['last_name'],
                    'email' => $teacherData['email'],
                    'password' => Hash::make('password123'),
                    'school_type' => 'university',
                    'school_id' => $universitySchool->id
                ]);

                // Créer l'enseignant
                $teacher = Teacher::create([
                    'user_id' => $user->id,
                    'first_name' => $teacherData['first_name'],
                    'last_name' => $teacherData['last_name'],
                    'phone' => $teacherData['phone'],
                    'specialization' => $teacherData['specialization'],
                    'status' => 'active',
                    'school_id' => $universitySchool->id,
                    'employee_id' => $teacherData['employee_id'],
                    'hire_date' => now()->subYears(rand(1, 10))->subMonths(rand(1, 11)),
                    'qualifications' => $teacherData['qualifications'],
                    'ufr_id' => $department->ufr_id,
                    'department_id' => $department->id,
                    'academic_rank' => $teacherData['academic_rank'],
                    'research_interests' => $teacherData['research_interests'],
                    'office_location' => $teacherData['office_location'],
                    'salary' => $teacherData['salary'],
                ]);

                $this->info("✅ Enseignant créé: {$teacher->first_name} {$teacher->last_name} ({$teacher->academic_rank})");
            }

            DB::commit();
            
            // Statistiques finales
            $this->info("\n📊 Statistiques:");
            $this->info("   - UFR: " . UFR::where('school_id', $universitySchool->id)->count());
            $this->info("   - Départements: " . Department::where('school_id', $universitySchool->id)->count());
            $this->info("   - Enseignants universitaires: " . Teacher::where('school_id', $universitySchool->id)->whereNotNull('ufr_id')->count());
            
            $this->info("\n🎉 Données de test universitaires créées avec succès !");
            $this->info("🌐 Visitez: http://localhost:8000/university/teachers");
            $this->info("🔑 Mot de passe par défaut: password123");
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Erreur: " . $e->getMessage());
            return 1;
        }
    }
}
