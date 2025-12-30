<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GradePeriod;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Page principale des paramètres
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Gestion des informations générales de l'école
     */
    public function school()
    {
        $settings = [
            'school_name' => Setting::get('school_name', 'ENMA School'),
            'school_address' => Setting::get('school_address', ''),
            'school_phone' => Setting::get('school_phone', ''),
            'school_email' => Setting::get('school_email', ''),
            'school_website' => Setting::get('school_website', ''),
            'school_logo' => Setting::get('school_logo', ''),
            'currency' => Setting::get('currency', 'FCFA'),
            'timezone' => Setting::get('timezone', 'Africa/Abidjan'),
        ];

        return view('settings.school', compact('settings'));
    }

    /**
     * Mise à jour des informations de l'école
     */
    public function updateSchool(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'nullable|string|max:500',
            'school_phone' => 'nullable|string|max:20',
            'school_email' => 'nullable|email|max:255',
            'school_website' => 'nullable|url|max:255',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'school_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $settings = $request->except(['_token', '_method', 'school_logo']);
        
        // Gestion du logo
        if ($request->hasFile('school_logo')) {
            $logoPath = $request->file('school_logo')->store('logos', 'public');
            $settings['school_logo'] = $logoPath;
        }

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.school')
            ->with('success', 'Les informations de l\'école ont été mises à jour avec succès.');
    }

    /**
     * Gestion des années scolaires
     */
    public function years()
    {
        $years = AcademicYear::with('gradePeriods')->orderBy('start_date', 'desc')->get();
        
        return view('settings.years', compact('years'));
    }

    /**
     * Créer une nouvelle année scolaire
     */
    public function storeYear(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_current' => 'sometimes|boolean',
        ]);

        // Si cette année est définie comme actuelle, désactiver les autres
        if ($request->is_current) {
            AcademicYear::query()->update(['is_current' => false]);
        }

        $year = AcademicYear::create($request->all());

        // Créer les périodes par défaut (3 trimestres)
        $this->createDefaultPeriods($year);

        return redirect()->route('settings.years')
            ->with('success', 'L\'année scolaire a été créée avec succès.');
    }

    /**
     * Archiver/désarchiver une année scolaire
     */
    public function archiveYear(AcademicYear $year)
    {
        $year->update(['is_archived' => !$year->is_archived]);
        
        $status = $year->is_archived ? 'archivée' : 'désarchivée';
        
        return redirect()->route('settings.years')
            ->with('success', "L'année scolaire a été {$status} avec succès.");
    }

    /**
     * Système de notation
     */
    public function grading()
    {
        $gradingSettings = [
            'grading_scale' => Setting::get('grading_scale', '20'), // 20, 100, ou letter
            'passing_grade' => Setting::get('passing_grade', '10'),
            'excellence_grade' => Setting::get('excellence_grade', '16'),
            'grade_precision' => Setting::get('grade_precision', '2'), // nombre de décimales
            'display_letter_grades' => Setting::get('display_letter_grades', false),
            'letter_grade_scale' => Setting::get('letter_grade_scale', [
                'A+' => ['min' => 18, 'max' => 20],
                'A' => ['min' => 16, 'max' => 17.99],
                'B+' => ['min' => 14, 'max' => 15.99],
                'B' => ['min' => 12, 'max' => 13.99],
                'C' => ['min' => 10, 'max' => 11.99],
                'D' => ['min' => 8, 'max' => 9.99],
                'F' => ['min' => 0, 'max' => 7.99],
            ]),
        ];

        return view('settings.grading', compact('gradingSettings'));
    }

    /**
     * Mise à jour du système de notation
     */
    public function updateGrading(Request $request)
    {
        $request->validate([
            'grading_scale' => 'required|in:20,100,letter',
            'passing_grade' => 'required|numeric|min:0',
            'excellence_grade' => 'required|numeric|min:0',
            'grade_precision' => 'required|integer|min:0|max:4',
            'display_letter_grades' => 'sometimes|boolean',
        ]);

        $settings = $request->except(['_token', '_method']);
        $settings['display_letter_grades'] = $request->has('display_letter_grades');

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.grading')
            ->with('success', 'Les paramètres de notation ont été mis à jour avec succès.');
    }

    /**
     * Paramètres financiers globaux
     */
    public function financial()
    {
        $financialSettings = [
            'default_currency' => Setting::get('default_currency', 'FCFA'),
            'payment_terms_days' => Setting::get('payment_terms_days', '30'),
            'late_payment_fee_percentage' => Setting::get('late_payment_fee_percentage', '5'),
            'enable_payment_reminders' => Setting::get('enable_payment_reminders', true),
            'reminder_days_before' => Setting::get('reminder_days_before', '7,3,1'),
            'bank_name' => Setting::get('bank_name', ''),
            'bank_account_number' => Setting::get('bank_account_number', ''),
            'bank_swift_code' => Setting::get('bank_swift_code', ''),
            'enable_online_payments' => Setting::get('enable_online_payments', false),
        ];

        return view('settings.financial', compact('financialSettings'));
    }

    /**
     * Mise à jour des paramètres financiers
     */
    public function updateFinancial(Request $request)
    {
        $request->validate([
            'default_currency' => 'required|string|max:10',
            'payment_terms_days' => 'required|integer|min:1|max:365',
            'late_payment_fee_percentage' => 'required|numeric|min:0|max:100',
            'enable_payment_reminders' => 'sometimes|boolean',
            'reminder_days_before' => 'required|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_swift_code' => 'nullable|string|max:20',
            'enable_online_payments' => 'sometimes|boolean',
        ]);

        $settings = $request->except(['_token', '_method']);
        $settings['enable_payment_reminders'] = $request->has('enable_payment_reminders');
        $settings['enable_online_payments'] = $request->has('enable_online_payments');

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.financial')
            ->with('success', 'Les paramètres financiers ont été mis à jour avec succès.');
    }

    /**
     * Créer les périodes par défaut pour une année scolaire
     */
    private function createDefaultPeriods(AcademicYear $year)
    {
        $startDate = $year->start_date;
        $endDate = $year->end_date;
        
        // Calculer les dates de trimestres
        $totalDays = $startDate->diffInDays($endDate);
        $trimesterDays = $totalDays / 3;

        $periods = [
            [
                'name' => '1er Trimestre',
                'start_date' => $startDate,
                'end_date' => $startDate->copy()->addDays($trimesterDays),
                'academic_year_id' => $year->id,
                'type' => 'trimester',
                'order' => 1,
            ],
            [
                'name' => '2ème Trimestre',
                'start_date' => $startDate->copy()->addDays($trimesterDays + 1),
                'end_date' => $startDate->copy()->addDays($trimesterDays * 2),
                'academic_year_id' => $year->id,
                'type' => 'trimester',
                'order' => 2,
            ],
            [
                'name' => '3ème Trimestre',
                'start_date' => $startDate->copy()->addDays($trimesterDays * 2 + 1),
                'end_date' => $endDate,
                'academic_year_id' => $year->id,
                'type' => 'trimester',
                'order' => 3,
            ],
        ];

        foreach ($periods as $period) {
            GradePeriod::create($period);
        }
    }
}