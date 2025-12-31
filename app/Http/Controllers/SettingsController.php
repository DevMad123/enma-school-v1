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
     * Redirection vers la gouvernance de l'établissement (nouveau système)
     * @deprecated Utiliser Admin\SchoolController à la place
     */
    public function school()
    {
        return redirect()->route('admin.schools.index')
            ->with('info', 'La gestion de l\'établissement a été déplacée vers la section Gouvernance.');
    }

    /**
     * Redirection vers la gouvernance de l'établissement (nouveau système)
     * @deprecated Utiliser Admin\SchoolController à la place
     */
    public function updateSchool(Request $request)
    {
        return redirect()->route('admin.schools.index')
            ->with('info', 'La gestion de l\'établissement a été déplacée vers la section Gouvernance.');
    }

    /**
     * Redirection vers la gouvernance pour la gestion des années scolaires
     * @deprecated Utiliser Admin\SchoolController à la place
     */
    public function years()
    {
        return redirect()->route('admin.schools.index')
            ->with('info', 'La gestion des années scolaires a été déplacée vers la section Gouvernance.');
    }

    /**
     * Redirection vers la gouvernance pour la gestion des années scolaires
     * @deprecated Utiliser Admin\SchoolController à la place
     */
    public function storeYear(Request $request)
    {
        return redirect()->route('admin.schools.index')
            ->with('info', 'La gestion des années scolaires a été déplacée vers la section Gouvernance.');
    // }

        // Créer les périodes par défaut (3 trimestres)
        // $this->createDefaultPeriods($year);

        // return redirect()->route('settings.years')
        //     ->with('success', 'L\'année scolaire a été créée avec succès.');
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
     * Redirection vers la gouvernance pour le système de notation
     * @deprecated Utiliser Admin\SchoolController à la place
     */
    public function grading()
    {
        return redirect()->route('admin.schools.index')
            ->with('info', 'La gestion du système de notation a été déplacée vers la section Gouvernance.');
    }

    /**
     * Redirection vers la gouvernance pour le système de notation
     * @deprecated Utiliser Admin\SchoolController à la place
     */
    public function updateGrading(Request $request)
    {
        return redirect()->route('admin.schools.index')
            ->with('info', 'La gestion du système de notation a été déplacée vers la section Gouvernance.');
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
     * Paramètres système et de sécurité
     */
    public function system()
    {
        $systemSettings = [
            'app_name' => Setting::get('app_name', 'ENMA School'),
            'app_version' => Setting::get('app_version', '1.0.0'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'debug_mode' => Setting::get('debug_mode', false),
            'session_timeout' => Setting::get('session_timeout', '120'), // minutes
            'file_upload_max_size' => Setting::get('file_upload_max_size', '10'), // MB
            'backup_frequency' => Setting::get('backup_frequency', 'daily'),
            'enable_audit_logs' => Setting::get('enable_audit_logs', true),
        ];

        return view('settings.system', compact('systemSettings'));
    }

    /**
     * Mise à jour des paramètres système
     */
    public function updateSystem(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'session_timeout' => 'required|integer|min:5|max:1440',
            'file_upload_max_size' => 'required|integer|min:1|max:100',
            'backup_frequency' => 'required|in:daily,weekly,monthly',
        ]);

        $settings = $request->except(['_token', '_method']);
        $settings['maintenance_mode'] = $request->has('maintenance_mode');
        $settings['debug_mode'] = $request->has('debug_mode');
        $settings['enable_audit_logs'] = $request->has('enable_audit_logs');

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.system')
            ->with('success', 'Les paramètres système ont été mis à jour avec succès.');
    }

    /**
     * Paramètres d'email et notifications
     */
    public function notifications()
    {
        $notificationSettings = [
            'smtp_host' => Setting::get('smtp_host', ''),
            'smtp_port' => Setting::get('smtp_port', '587'),
            'smtp_username' => Setting::get('smtp_username', ''),
            'smtp_encryption' => Setting::get('smtp_encryption', 'tls'),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', ''),
            'enable_email_notifications' => Setting::get('enable_email_notifications', true),
            'enable_sms_notifications' => Setting::get('enable_sms_notifications', false),
        ];

        return view('settings.notifications', compact('notificationSettings'));
    }

    /**
     * Mise à jour des paramètres de notifications
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|email|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $settings = $request->except(['_token', '_method']);
        $settings['enable_email_notifications'] = $request->has('enable_email_notifications');
        $settings['enable_sms_notifications'] = $request->has('enable_sms_notifications');

        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('settings.notifications')
            ->with('success', 'Les paramètres de notifications ont été mis à jour avec succès.');
    }
}