<?php

if (!function_exists('ordinal')) {
    /**
     * Convert a number to its ordinal representation in French
     *
     * @param int $number
     * @return string
     */
    function ordinal($number) {
        if ($number == 1) {
            return '1er';
        }
        
        return $number . 'ème';
    }
}

if (!function_exists('percentage')) {
    /**
     * Calculate percentage
     *
     * @param float $value
     * @param float $total
     * @return float
     */
    function percentage($value, $total) {
        if ($total == 0) return 0;
        return round(($value / $total) * 100, 1);
    }
}

if (!function_exists('grade_color_class')) {
    /**
     * Get Tailwind CSS color classes based on grade
     *
     * @param float $grade
     * @param float $max
     * @return string
     */
    function grade_color_class($grade, $max = 20) {
        $percentage = ($grade / $max) * 100;
        
        if ($percentage >= 80) return 'green'; // 16+/20
        if ($percentage >= 60) return 'yellow'; // 12-15/20
        if ($percentage >= 50) return 'blue'; // 10-12/20
        
        return 'red'; // <10/20
    }
}

if (!function_exists('format_currency')) {
    /**
     * Format currency in FCFA
     *
     * @param int|float $amount
     * @param bool $showCurrency
     * @return string
     */
    function format_currency($amount, $showCurrency = true) {
        $formatted = number_format($amount, 0, ',', ' ');
        return $showCurrency ? $formatted . ' FCFA' : $formatted;
    }
}

if (!function_exists('trend_indicator')) {
    /**
     * Generate trend indicator HTML
     *
     * @param string $type ('up', 'down', 'stable')
     * @param float $value
     * @param string $label
     * @return string
     */
    function trend_indicator($type, $value, $label = '') {
        $colors = [
            'up' => 'text-green-600',
            'down' => 'text-red-600', 
            'stable' => 'text-gray-600'
        ];
        
        $icons = [
            'up' => '↗',
            'down' => '↘',
            'stable' => '→'
        ];
        
        $color = $colors[$type] ?? $colors['stable'];
        $icon = $icons[$type] ?? $icons['stable'];
        
        return "<span class=\"{$color} text-xs font-medium\">{$icon} {$value}{$label}</span>";
    }
}

if (!function_exists('time_ago')) {
    /**
     * Convert timestamp to human readable format in French
     *
     * @param \Carbon\Carbon $date
     * @return string
     */
    function time_ago($date) {
        if (!$date instanceof \Carbon\Carbon) {
            $date = \Carbon\Carbon::parse($date);
        }
        
        return $date->locale('fr')->diffForHumans();
    }
}

if (!function_exists('academic_year_display')) {
    /**
     * Format academic year for display
     *
     * @param int|null $year
     * @return string
     */
    function academic_year_display($year = null) {
        $currentYear = $year ?? date('Y');
        return $currentYear . '-' . ($currentYear + 1);
    }
}

if (!function_exists('student_status_badge')) {
    /**
     * Generate student status badge
     *
     * @param string $status
     * @return string
     */
    function student_status_badge($status) {
        $badges = [
            'active' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Actif</span>',
            'inactive' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactif</span>',
            'suspended' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Suspendu</span>',
            'graduated' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Diplômé</span>',
        ];
        
        return $badges[$status] ?? $badges['active'];
    }
}

if (!function_exists('school')) {
    /**
     * Get the active school instance
     *
     * @return \App\Models\School|null
     */
    function school() {
        return \App\Models\School::getActiveSchool();
    }
}

if (!function_exists('school_setting')) {
    /**
     * Get a school setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function school_setting($key, $default = null) {
        $school = school();
        return $school ? $school->getSetting($key, $default) : $default;
    }
}

// ============================================================================
// Module A6 - Supervision & Audits Helpers
// ============================================================================

if (!function_exists('activity_text')) {
    /**
     * Get human-readable activity text
     *
     * @param object $activity
     * @return string
     */
    function activity_text($activity) {
        return \App\Helpers\ActivityHelper::getActivityText($activity);
    }
}

if (!function_exists('action_text')) {
    /**
     * Get human-readable action text
     *
     * @param string $action
     * @return string
     */
    function action_text($action) {
        return \App\Helpers\ActivityHelper::getActionText($action);
    }
}

if (!function_exists('activity_color')) {
    /**
     * Get activity color class
     *
     * @param string $action
     * @return string
     */
    function activity_color($action) {
        return \App\Helpers\ActivityHelper::getActivityColor($action);
    }
}

if (!function_exists('entity_icon')) {
    /**
     * Get entity icon
     *
     * @param string $entity
     * @return string
     */
    function entity_icon($entity) {
        return \App\Helpers\ActivityHelper::getEntityIcon($entity);
    }
}

if (!function_exists('getActivityDescription')) {
    /**
     * Get human-readable description for activity logs
     *
     * @param \App\Models\ActivityLog $activity
     * @return string
     */
    function getActivityDescription($activity) {
        $actions = [
            'created_evaluation' => 'a créé une évaluation',
            'updated_evaluation' => 'a modifié une évaluation',
            'deleted_evaluation' => 'a supprimé une évaluation',
            'created_grade' => 'a ajouté une note',
            'updated_grade' => 'a modifié une note',
            'created_assignment' => 'a créé un devoir',
            'updated_assignment' => 'a mis à jour un devoir',
            'created_lesson' => 'a créé une leçon',
            'uploaded_file' => 'a téléchargé un fichier',
            'sent_message' => 'a envoyé un message',
            'created_announcement' => 'a publié une annonce',
            'joined_class' => 'a rejoint une classe',
            'completed_assignment' => 'a terminé un devoir',
            'submitted_homework' => 'a rendu un devoir',
            'viewed_lesson' => 'a consulté une leçon',
            'downloaded_resource' => 'a téléchargé une ressource',
            'logged_in' => 's\'est connecté(e)',
            'logged_out' => 's\'est déconnecté(e)',
            'profile_updated' => 'a mis à jour son profil',
            'password_changed' => 'a changé son mot de passe',
        ];

        $action = $activity->action ?? 'unknown_action';
        $description = $actions[$action] ?? "a effectué l'action: " . $action;

        // Ajouter des détails contextuels si disponibles
        if ($activity->details && is_array($activity->details)) {
            $details = $activity->details;
            
            if (isset($details['subject'])) {
                $description .= " en " . $details['subject'];
            }
            
            if (isset($details['class'])) {
                $description .= " (classe: " . $details['class'] . ")";
            }
            
            if (isset($details['grade']) && $action == 'created_grade') {
                $description .= " (" . $details['grade'] . "/20)";
            }
        }

        return $description;
    }
}