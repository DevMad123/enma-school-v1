<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SecurityController extends Controller {

    /**
     * Paramètres de sécurité
     */
    public function settings()
    {
        $securitySettings = [
            // Paramètres de mot de passe
            'password_min_length' => Setting::get('password_min_length', 8),
            'password_require_uppercase' => Setting::get('password_require_uppercase', false),
            'password_require_lowercase' => Setting::get('password_require_lowercase', false),
            'password_require_numbers' => Setting::get('password_require_numbers', false),
            'password_require_symbols' => Setting::get('password_require_symbols', false),
            'password_expiry_days' => Setting::get('password_expiry_days', 90),
            
            // Paramètres de session
            'session_timeout_minutes' => Setting::get('session_timeout_minutes', 120),
            'max_login_attempts' => Setting::get('max_login_attempts', 5),
            'lockout_duration_minutes' => Setting::get('lockout_duration_minutes', 15),
            
            // Paramètres d'activité
            'enable_user_activity_log' => Setting::get('enable_user_activity_log', true),
            'enable_login_notifications' => Setting::get('enable_login_notifications', false),
            'force_email_verification' => Setting::get('force_email_verification', true),
            
            // Paramètres de sécurité avancés
            'enable_two_factor_auth' => Setting::get('enable_two_factor_auth', false),
            'require_password_change_on_first_login' => Setting::get('require_password_change_on_first_login', true),
            'enable_ip_whitelist' => Setting::get('enable_ip_whitelist', false),
            'allowed_ip_addresses' => Setting::get('allowed_ip_addresses', ''),
        ];

        return view('security.settings', compact('securitySettings'));
    }

    /**
     * Mise à jour des paramètres de sécurité
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'password_min_length' => 'required|integer|min:6|max:128',
            'password_require_uppercase' => 'sometimes|boolean',
            'password_require_lowercase' => 'sometimes|boolean',
            'password_require_numbers' => 'sometimes|boolean',
            'password_require_symbols' => 'sometimes|boolean',
            'password_expiry_days' => 'required|integer|min:0|max:365',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
            'max_login_attempts' => 'required|integer|min:1|max:20',
            'lockout_duration_minutes' => 'required|integer|min:1|max:1440',
            'enable_user_activity_log' => 'sometimes|boolean',
            'enable_login_notifications' => 'sometimes|boolean',
            'force_email_verification' => 'sometimes|boolean',
            'enable_two_factor_auth' => 'sometimes|boolean',
            'require_password_change_on_first_login' => 'sometimes|boolean',
            'enable_ip_whitelist' => 'sometimes|boolean',
            'allowed_ip_addresses' => 'nullable|string',
        ]);

        $settings = $request->except(['_token', '_method']);
        
        // Traitement des checkboxes
        $booleanFields = [
            'password_require_uppercase',
            'password_require_lowercase', 
            'password_require_numbers',
            'password_require_symbols',
            'enable_user_activity_log',
            'enable_login_notifications',
            'force_email_verification',
            'enable_two_factor_auth',
            'require_password_change_on_first_login',
            'enable_ip_whitelist'
        ];

        foreach ($booleanFields as $field) {
            $settings[$field] = $request->has($field);
        }

        // Sauvegarder chaque paramètre
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('security.settings')
            ->with('success', 'Les paramètres de sécurité ont été mis à jour avec succès.');
    }

    /**
     * Journal d'activité des utilisateurs
     */
    public function activityLog(Request $request)
    {
        // Cette méthode nécessiterait un modèle UserActivity
        // Pour l'instant, on retourne une vue vide
        
        return view('security.activity-log');
    }

    /**
     * Audit de sécurité
     */
    public function securityAudit()
    {
        $auditResults = [
            'weak_passwords' => $this->checkWeakPasswords(),
            'inactive_users' => $this->checkInactiveUsers(),
            'admin_users' => $this->checkAdminUsers(),
            'unverified_emails' => $this->checkUnverifiedEmails(),
            'security_score' => 0,
        ];

        // Calculer le score de sécurité
        $auditResults['security_score'] = $this->calculateSecurityScore($auditResults);

        return view('security.audit', compact('auditResults'));
    }

    /**
     * Vérification des mots de passe faibles
     */
    private function checkWeakPasswords()
    {
        // Cette vérification nécessiterait une logique plus complexe
        // Pour l'instant, retourner un nombre simulé
        return 3;
    }

    /**
     * Vérification des utilisateurs inactifs
     */
    private function checkInactiveUsers()
    {
        return \App\Models\User::where('last_login_at', '<', now()->subDays(90))
            ->orWhereNull('last_login_at')
            ->count();
    }

    /**
     * Vérification des utilisateurs administrateurs
     */
    private function checkAdminUsers()
    {
        return \App\Models\User::role(['super_admin', 'admin'])->count();
    }

    /**
     * Vérification des emails non vérifiés
     */
    private function checkUnverifiedEmails()
    {
        return \App\Models\User::whereNull('email_verified_at')->count();
    }

    /**
     * Calcul du score de sécurité
     */
    private function calculateSecurityScore($audit)
    {
        $score = 100;
        
        // Déduction pour les problèmes de sécurité
        $score -= $audit['weak_passwords'] * 5;
        $score -= $audit['inactive_users'] * 2;
        $score -= $audit['admin_users'] > 3 ? 10 : 0;
        $score -= $audit['unverified_emails'] * 3;

        return max(0, min(100, $score));
    }
}