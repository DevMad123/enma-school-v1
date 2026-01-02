<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paramètres de Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Configuration des limites de taux pour différents types d'opérations.
    | Les valeurs peuvent être surchargées par utilisateur selon le rôle.
    |
    */
    'rate_limits' => [
        'auth' => [
            'base' => [
                '1min' => 5,
                '15min' => 15,
                '1hour' => 50
            ],
            'multipliers' => [
                'super_admin' => 5,
                'admin' => 3,
                'teacher' => 2,
                'student' => 1
            ]
        ],
        'user_creation' => [
            'base' => [
                '1min' => 2,
                '1hour' => 10,
                '1day' => 50
            ],
            'multipliers' => [
                'super_admin' => 10,
                'admin' => 5,
                'teacher' => 1,
                'student' => 0
            ]
        ],
        'financial' => [
            'base' => [
                '1min' => 3,
                '15min' => 20,
                '1hour' => 100
            ],
            'multipliers' => [
                'super_admin' => 5,
                'admin' => 3,
                'finance_manager' => 2,
                'teacher' => 1,
                'student' => 0
            ]
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation de Mot de Passe
    |--------------------------------------------------------------------------
    |
    | Paramètres pour la validation des mots de passe et la sécurité.
    |
    */
    'password' => [
        'min_length' => 8,
        'require_mixed_case' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'check_compromised' => true,
        'history_count' => 5, // Nb de derniers mots de passe à retenir
        'expires_after_days' => 90,
        'warning_days_before_expiry' => 7
    ],

    /*
    |--------------------------------------------------------------------------
    | Sessions et Sécurité
    |--------------------------------------------------------------------------
    |
    | Configuration pour les sessions et la sécurité générale.
    |
    */
    'session' => [
        'timeout_minutes' => 120, // 2 heures
        'concurrent_sessions_limit' => 3,
        'force_logout_on_role_change' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit et Logging
    |--------------------------------------------------------------------------
    |
    | Configuration pour l'audit de sécurité et le logging.
    |
    */
    'audit' => [
        'log_all_user_actions' => true,
        'log_failed_logins' => true,
        'log_permission_denials' => true,
        'retain_logs_days' => 365,
        'critical_actions' => [
            'user_creation',
            'user_deletion',
            'role_assignment',
            'permission_grant',
            'financial_transaction',
            'password_reset',
            'data_export'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation des Emails
    |--------------------------------------------------------------------------
    |
    | Configuration pour la validation des adresses email.
    |
    */
    'email_validation' => [
        'block_disposable' => true,
        'require_mx_record' => true,
        'blocked_domains' => [
            'tempmail.org',
            '10minutemail.com',
            'guerrillamail.com',
            'mailinator.com',
            'throwaway.email'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Protection CSRF
    |--------------------------------------------------------------------------
    |
    | Configuration pour la protection contre les attaques CSRF.
    |
    */
    'csrf' => [
        'ajax_auto_include' => true,
        'token_lifetime' => 3600, // 1 heure
        'verify_referer' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Restrictions IP
    |--------------------------------------------------------------------------
    |
    | Configuration pour les restrictions d'adresses IP.
    |
    */
    'ip_restrictions' => [
        'enabled' => false,
        'whitelist' => [
            // '192.168.1.0/24',
            // '10.0.0.0/8'
        ],
        'blacklist' => [
            // IPs bloquées
        ],
        'max_failed_attempts_per_ip' => 10,
        'ban_duration_hours' => 24
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation des Fichiers
    |--------------------------------------------------------------------------
    |
    | Configuration pour la sécurité des uploads de fichiers.
    |
    */
    'file_upload' => [
        'max_size_mb' => 10,
        'allowed_image_types' => ['jpeg', 'jpg', 'png', 'webp'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'scan_for_viruses' => false, // Nécessite ClamAV
        'check_file_headers' => true,
        'quarantine_suspicious' => true
    ]
];