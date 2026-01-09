<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\School;
use App\Models\AcademicYear;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Hash;

class TestEnvironmentSeeder extends Seeder
{
    /**
     * Run the database seeder pour l'environnement de test.
     */
    public function run(): void
    {
        $this->createPermissions();
        $this->createRoles();
        $this->createAcademicYear();
        $this->createTestSchools();
        $this->createTestUsers();
    }

    private function createPermissions(): void
    {
        $permissions = [
            // Permissions générales
            'view_dashboard',
            'manage_users',
            'manage_schools',
            
            // Permissions académiques
            'view_students',
            'manage_students',
            'view_enrollments',
            'manage_enrollments',
            'view_evaluations',
            'manage_evaluations',
            'view_grades',
            'manage_grades',
            
            // Permissions universitaires
            'view_university_programs',
            'manage_university_programs',
            'view_course_units',
            'manage_course_units',
            
            // Permissions configuration
            'view_educational_settings',
            'manage_educational_settings',
            'view_global_settings',
            'update_global_settings',
            'view_school_settings',
            'update_school_settings',
            'audit_settings',
            
            // Permissions documents
            'generate_documents',
            'manage_document_templates',
            
            // Permissions reporting
            'view_reports',
            'export_data'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }

    private function createRoles(): void
    {
        // Super Admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin Système
        $systemAdmin = Role::firstOrCreate(['name' => 'system_admin']);
        $systemAdmin->givePermissionTo([
            'view_dashboard',
            'manage_users',
            'manage_schools',
            'view_global_settings',
            'update_global_settings',
            'audit_settings'
        ]);

        // Admin École
        $schoolAdmin = Role::firstOrCreate(['name' => 'school_admin']);
        $schoolAdmin->givePermissionTo([
            'view_dashboard',
            'view_students',
            'manage_students',
            'view_enrollments',
            'manage_enrollments',
            'view_educational_settings',
            'manage_educational_settings',
            'view_school_settings',
            'update_school_settings',
            'view_reports',
            'generate_documents'
        ]);

        // Admin Général
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'view_dashboard',
            'view_students',
            'manage_students',
            'view_enrollments',
            'manage_enrollments',
            'view_evaluations',
            'manage_evaluations',
            'view_grades',
            'manage_grades',
            'view_university_programs',
            'manage_university_programs',
            'view_course_units',
            'manage_course_units',
            'view_educational_settings',
            'view_reports',
            'generate_documents'
        ]);

        // Directeur
        $director = Role::firstOrCreate(['name' => 'director']);
        $director->givePermissionTo([
            'view_dashboard',
            'view_students',
            'view_enrollments',
            'view_evaluations',
            'view_grades',
            'view_university_programs',
            'view_course_units',
            'view_educational_settings',
            'view_reports',
            'generate_documents'
        ]);

        // Enseignant
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $teacher->givePermissionTo([
            'view_dashboard',
            'view_students',
            'view_enrollments',
            'view_evaluations',
            'manage_evaluations',
            'view_grades',
            'manage_grades'
        ]);

        // Surveillant
        $supervisor = Role::firstOrCreate(['name' => 'supervisor']);
        $supervisor->givePermissionTo([
            'view_dashboard',
            'view_students',
            'view_enrollments'
        ]);
    }

    private function createAcademicYear(): void
    {
        AcademicYear::firstOrCreate([
            'name' => '2025-2026'
        ], [
            'start_date' => '2025-10-01',
            'end_date' => '2026-07-31',
            'is_current' => true
        ]);
    }

    private function createTestSchools(): void
    {
        // École préuniversitaire
        School::firstOrCreate([
            'name' => 'École Préuniversitaire Test'
        ], [
            'short_name' => 'EPT',
            'type' => 'pre_university',
            'email' => 'admin@ecole-test.ci',
            'phone' => '+225 01 23 45 67 89',
            'address' => '123 Avenue de la République',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'educational_levels' => ['primary', 'secondary'],
            'academic_system' => 'trimestre',
            'grading_system' => '20',
            'is_active' => true
        ]);

        // Université test
        School::firstOrCreate([
            'name' => 'Université Test'
        ], [
            'short_name' => 'UT',
            'type' => 'university',
            'email' => 'admin@universite-test.ci',
            'phone' => '+225 01 98 76 54 32',
            'address' => '456 Boulevard des Universités',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'educational_levels' => ['undergraduate', 'graduate'],
            'academic_system' => 'semestre',
            'grading_system' => '20',
            'is_active' => true
        ]);
    }

    private function createTestUsers(): void
    {
        $preunivSchool = School::where('type', 'pre_university')->first();
        $univSchool = School::where('type', 'university')->first();

        // Super Admin
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@enmaschool.ci'
        ], [
            'name' => 'Super Administrateur',
            'password' => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $superAdmin->assignRole('super_admin');

        // Admin École Préuniv
        if ($preunivSchool) {
            $preunivAdmin = User::firstOrCreate([
                'email' => 'admin@ecole-test.ci'
            ], [
                'name' => 'Admin École Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'school_id' => $preunivSchool->id
            ]);
            $preunivAdmin->assignRole('school_admin');
        }

        // Admin Université
        if ($univSchool) {
            $univAdmin = User::firstOrCreate([
                'email' => 'admin@universite-test.ci'
            ], [
                'name' => 'Admin Université Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'school_id' => $univSchool->id
            ]);
            $univAdmin->assignRole('school_admin');
        }

        // Enseignant test
        if ($preunivSchool) {
            $teacher = User::firstOrCreate([
                'email' => 'enseignant@ecole-test.ci'
            ], [
                'name' => 'Enseignant Test',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'school_id' => $preunivSchool->id
            ]);
            $teacher->assignRole('teacher');
        }

        // Admin général (pour tests)
        $admin = User::firstOrCreate([
            'email' => 'admin@test.ci'
        ], [
            'name' => 'Admin Test',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'school_id' => $preunivSchool ? $preunivSchool->id : null
        ]);
        $admin->assignRole('admin');
    }
}