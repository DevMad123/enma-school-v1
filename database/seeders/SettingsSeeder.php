<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSettings = [
            // École
            'school_name' => 'ENMA School',
            'school_address' => '',
            'school_phone' => '',
            'school_email' => '',
            'school_website' => '',
            'school_logo' => '',
            'currency' => 'FCFA',
            'timezone' => 'Africa/Abidjan',

            // Système de notation
            'grading_scale' => '20',
            'passing_grade' => '10',
            'excellence_grade' => '16',
            'grade_precision' => '2',
            'display_letter_grades' => false,
            'letter_grade_scale' => [
                'A+' => ['min' => 18, 'max' => 20],
                'A' => ['min' => 16, 'max' => 17.99],
                'B+' => ['min' => 14, 'max' => 15.99],
                'B' => ['min' => 12, 'max' => 13.99],
                'C' => ['min' => 10, 'max' => 11.99],
                'D' => ['min' => 8, 'max' => 9.99],
                'F' => ['min' => 0, 'max' => 7.99],
            ],

            // Paramètres financiers
            'default_currency' => 'FCFA',
            'payment_terms_days' => '30',
            'late_payment_fee_percentage' => '5',
            'enable_payment_reminders' => true,
            'reminder_days_before' => '7,3,1',
            'bank_name' => '',
            'bank_account_number' => '',
            'bank_swift_code' => '',
            'enable_online_payments' => false,
        ];

        foreach ($defaultSettings as $key => $value) {
            Setting::set($key, $value);
        }
    }
}