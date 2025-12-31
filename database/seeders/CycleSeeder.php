<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cycle;

class CycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cycles = [
            [
                'name' => 'Premier Cycle',
                'code' => 'PC',
                'description' => 'Classes de 6ème, 5ème, 4ème, 3ème',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Second Cycle',
                'code' => 'SC',
                'description' => 'Classes de 2nde, 1ère, Terminale',
                'order' => 2,
                'is_active' => true,
            ],
        ];

        foreach ($cycles as $cycleData) {
            Cycle::firstOrCreate(
                ['code' => $cycleData['code']],
                $cycleData
            );
        }
    }
}