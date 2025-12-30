<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AcademicYearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Année académique courante 2024-2025
        AcademicYear::create([
            'name' => '2024-2025',
            'start_date' => Carbon::create(2024, 9, 1), // 1er septembre 2024
            'end_date' => Carbon::create(2025, 6, 30),  // 30 juin 2025
            'is_active' => true
        ]);

        // Année académique suivante 2025-2026
        AcademicYear::create([
            'name' => '2025-2026', 
            'start_date' => Carbon::create(2025, 9, 1), // 1er septembre 2025
            'end_date' => Carbon::create(2026, 6, 30),  // 30 juin 2026
            'is_active' => false
        ]);

        // Année académique précédente 2023-2024
        AcademicYear::create([
            'name' => '2023-2024',
            'start_date' => Carbon::create(2023, 9, 1), // 1er septembre 2023
            'end_date' => Carbon::create(2024, 6, 30),  // 30 juin 2024
            'is_active' => false
        ]);
    }
}