<?php

use App\Models\{User, Teacher, UFR, Department, School};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UniversityTeachersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtenir l'école universitaire
        $universitySchool = School::where('type', 'university')->first();
        
        if (!$universitySchool) {
            $this->command->error('Aucune école universitaire trouvée. Veuillez d\'abord créer une école universitaire.');
            return;
        }

        // Obtenir quelques UFR et départements
        $ufrs = UFR::all();
        $departments = Department::all();

        if ($ufrs->count() === 0) {
            $this->command->error('Aucune UFR trouvée. Veuillez d\'abord créer des UFR.');
            return;
        }

        // Enseignants universitaires fictifs
        $teachersData = [
            [
                'first_name' => 'Marie-Claire',
                'last_name' => 'KOUADIO',
                'email' => 'marie.kouadio@university.edu',
                'phone' => '+225 07 12 34 56',
                'academic_rank' => 'professeur',
                'specialization' => 'Mathématiques Appliquées',
                'research_interests' => 'Analyse numérique, Optimisation, Modélisation mathématique',
                'office_location' => 'Bureau 301, Bâtiment Sciences',
                'salary' => 850000,
                'employee_id' => 'UNI-2024-001',
                'qualifications' => "• Doctorat en Mathématiques - Université Paris-Saclay (2010)\n• HDR en Mathématiques Appliquées (2018)\n• Agrégation de Mathématiques (2005)",
            ],
            [
                'first_name' => 'Jean-Baptiste',
                'last_name' => 'KONE',
                'email' => 'jean.kone@university.edu',
                'phone' => '+225 05 87 65 43',
                'academic_rank' => 'maitre_de_conferences',
                'specialization' => 'Génie Informatique',
                'research_interests' => 'Intelligence artificielle, Machine learning, Traitement de données',
                'office_location' => 'Bureau 205, Bâtiment Informatique',
                'salary' => 650000,
                'employee_id' => 'UNI-2024-002',
                'qualifications' => "• Doctorat en Informatique - INRIA Sophia Antipolis (2015)\n• Master en Intelligence Artificielle - Université de Grenoble (2012)\n• Ingénieur en Informatique - ENSIMAG (2010)",
            ],
            [
                'first_name' => 'Aminata',
                'last_name' => 'DIALLO',
                'email' => 'aminata.diallo@university.edu',
                'phone' => '+225 07 98 76 54',
                'academic_rank' => 'maitre_assistant',
                'specialization' => 'Économie du Développement',
                'research_interests' => 'Économie rurale, Microfinance, Politiques publiques en Afrique',
                'office_location' => 'Bureau 102, Bâtiment Sciences Économiques',
                'salary' => 520000,
                'employee_id' => 'UNI-2024-003',
                'qualifications' => "• Doctorat en Sciences Économiques - Université Cheikh Anta Diop (2018)\n• Master en Économie du Développement - Université Paris 1 Panthéon-Sorbonne (2014)\n• Licence en Économie - Université d'Abidjan (2012)",
            ],
            [
                'first_name' => 'Désiré',
                'last_name' => 'YAPI',
                'email' => 'desire.yapi@university.edu',
                'phone' => '+225 05 44 33 22',
                'academic_rank' => 'assistant',
                'specialization' => 'Génie Civil',
                'research_interests' => 'Matériaux de construction locaux, Structures parasismiques',
                'office_location' => 'Bureau 404, Bâtiment Génie Civil',
                'salary' => 450000,
                'employee_id' => 'UNI-2024-004',
                'qualifications' => "• Master en Génie Civil - ENSTP Yaoundé (2020)\n• Ingénieur des Travaux Publics - INPHB Yamoussoukro (2018)\n• Formation en Construction Parasismique - École des Ponts ParisTech (2021)",
            ],
            [
                'first_name' => 'Fatou',
                'last_name' => 'TRAORE',
                'email' => 'fatou.traore@university.edu',
                'phone' => '+225 07 55 66 77',
                'academic_rank' => 'professeur_titulaire',
                'specialization' => 'Littérature Africaine',
                'research_interests' => 'Littérature orale africaine, Sociolinguistique, Patrimoine culturel',
                'office_location' => 'Bureau 201, Bâtiment Lettres',
                'salary' => 950000,
                'employee_id' => 'UNI-2024-005',
                'qualifications' => "• Doctorat d'État en Littérature Comparée - Université de la Sorbonne (2005)\n• Doctorat en Lettres Modernes - Université d'Abidjan (2000)\n• Agrégation de Lettres Modernes (1995)\n• Prix International de Littérature Africaine (2018)",
            ]
        ];

        foreach ($teachersData as $index => $teacherData) {
            // Créer l'utilisateur
            $user = User::create([
                'name' => $teacherData['first_name'] . ' ' . $teacherData['last_name'],
                'email' => $teacherData['email'],
                'password' => Hash::make('password123'),
                'school_type' => 'university',
            ]);

            // Assigner une UFR et un département aléatoirement
            $selectedUfr = $ufrs->random();
            $availableDepartments = $departments->where('ufr_id', $selectedUfr->id);
            $selectedDepartment = $availableDepartments->count() > 0 ? $availableDepartments->random() : null;

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
                'ufr_id' => $selectedUfr->id,
                'department_id' => $selectedDepartment?->id,
                'academic_rank' => $teacherData['academic_rank'],
                'research_interests' => $teacherData['research_interests'],
                'office_location' => $teacherData['office_location'],
                'salary' => $teacherData['salary'],
            ]);

            $this->command->info("Enseignant créé: {$teacher->first_name} {$teacher->last_name} ({$teacher->academic_rank})");
        }

        $this->command->info('Seeder d\'enseignants universitaires terminé avec succès !');
    }
}