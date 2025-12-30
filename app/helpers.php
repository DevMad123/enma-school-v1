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