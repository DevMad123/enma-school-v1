@extends('layouts.dashboard')

@section('title', 'Audit de Sécurité')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Audit de Sécurité</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Évaluation complète de la sécurité du système</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('security.settings') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Paramètres
                </a>
                <button onclick="window.location.reload()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser l'audit
                </button>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="mx-auto">
        <!-- Score de sécurité global -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Score de Sécurité Global</h3>
                    <div class="text-right">
                        <div class="text-3xl font-bold 
                            @if($auditResults['security_score'] >= 80) text-green-600
                            @elseif($auditResults['security_score'] >= 60) text-yellow-600
                            @else text-red-600
                            @endif">
                            {{ $auditResults['security_score'] }}/100
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($auditResults['security_score'] >= 80) Excellente sécurité
                            @elseif($auditResults['security_score'] >= 60) Sécurité convenable
                            @else Sécurité à améliorer
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Barre de progression -->
                <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700">
                    <div class="h-3 rounded-full 
                        @if($auditResults['security_score'] >= 80) bg-green-600
                        @elseif($auditResults['security_score'] >= 60) bg-yellow-600
                        @else bg-red-600
                        @endif" 
                        style="width: {{ $auditResults['security_score'] }}%"></div>
                </div>
                
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Dernière évaluation : {{ now()->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>

        <!-- Détails de l'audit -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Problèmes identifiés -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        Problèmes Identifiés
                    </h3>

                    <div class="space-y-4">
                        <!-- Mots de passe faibles -->
                        <div class="flex items-center justify-between p-3 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-800">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800 dark:text-red-200">Mots de passe faibles</p>
                                    <p class="text-xs text-red-600 dark:text-red-300">Utilisateurs avec des mots de passe non conformes</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-red-600">{{ $auditResults['weak_passwords'] }}</span>
                                <p class="text-xs text-red-500">utilisateurs</p>
                            </div>
                        </div>

                        <!-- Utilisateurs inactifs -->
                        <div class="flex items-center justify-between p-3 border border-yellow-200 rounded-lg bg-yellow-50 dark:bg-yellow-900/20 dark:border-yellow-800">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Comptes inactifs</p>
                                    <p class="text-xs text-yellow-600 dark:text-yellow-300">Aucune connexion depuis 90+ jours</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-yellow-600">{{ $auditResults['inactive_users'] }}</span>
                                <p class="text-xs text-yellow-500">comptes</p>
                            </div>
                        </div>

                        <!-- Emails non vérifiés -->
                        <div class="flex items-center justify-between p-3 border border-orange-200 rounded-lg bg-orange-50 dark:bg-orange-900/20 dark:border-orange-800">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-orange-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                    <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-orange-800 dark:text-orange-200">Emails non vérifiés</p>
                                    <p class="text-xs text-orange-600 dark:text-orange-300">Comptes sans validation d'email</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-orange-600">{{ $auditResults['unverified_emails'] }}</span>
                                <p class="text-xs text-orange-500">comptes</p>
                            </div>
                        </div>

                        <!-- Trop d'administrateurs -->
                        @if($auditResults['admin_users'] > 3)
                        <div class="flex items-center justify-between p-3 border border-purple-200 rounded-lg bg-purple-50 dark:bg-purple-900/20 dark:border-purple-800">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-purple-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-purple-800 dark:text-purple-200">Trop d'administrateurs</p>
                                    <p class="text-xs text-purple-600 dark:text-purple-300">Plus de 3 comptes admin actifs</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-bold text-purple-600">{{ $auditResults['admin_users'] }}</span>
                                <p class="text-xs text-purple-500">admins</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Recommandations -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Recommandations
                    </h3>

                    <div class="space-y-4">
                        @if($auditResults['weak_passwords'] > 0)
                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                Renforcer les politiques de mots de passe
                            </h4>
                            <p class="text-xs text-blue-600 dark:text-blue-300 mb-3">
                                Configurez des exigences plus strictes dans les paramètres de sécurité.
                            </p>
                            <a href="{{ route('security.settings') }}" 
                               class="text-xs text-blue-700 dark:text-blue-400 hover:underline">
                                Modifier les paramètres →
                            </a>
                        </div>
                        @endif

                        @if($auditResults['inactive_users'] > 0)
                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                Nettoyer les comptes inactifs
                            </h4>
                            <p class="text-xs text-blue-600 dark:text-blue-300 mb-3">
                                Désactivez ou supprimez les comptes inutilisés depuis longtemps.
                            </p>
                            <a href="{{ route('users.index', ['status' => 'inactive']) }}" 
                               class="text-xs text-blue-700 dark:text-blue-400 hover:underline">
                                Voir les comptes inactifs →
                            </a>
                        </div>
                        @endif

                        @if($auditResults['unverified_emails'] > 0)
                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50 dark:bg-blue-900/20 dark:border-blue-800">
                            <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                                Vérifier les adresses email
                            </h4>
                            <p class="text-xs text-blue-600 dark:text-blue-300 mb-3">
                                Forcez la vérification d'email ou supprimez les comptes non vérifiés.
                            </p>
                            <a href="{{ route('users.index') }}" 
                               class="text-xs text-blue-700 dark:text-blue-400 hover:underline">
                                Gérer les utilisateurs →
                            </a>
                        </div>
                        @endif

                        <!-- Recommandations générales -->
                        <div class="p-4 border border-green-200 rounded-lg bg-green-50 dark:bg-green-900/20 dark:border-green-800">
                            <h4 class="text-sm font-medium text-green-800 dark:text-green-200 mb-2">
                                Bonnes pratiques de sécurité
                            </h4>
                            <ul class="text-xs text-green-600 dark:text-green-300 space-y-1">
                                <li>• Activez l'authentification à deux facteurs (2FA)</li>
                                <li>• Configurez des sauvegardes automatiques régulières</li>
                                <li>• Surveillez régulièrement les journaux d'activité</li>
                                <li>• Mettez à jour le système régulièrement</li>
                                <li>• Formez les utilisateurs aux bonnes pratiques</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des audits -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6">
                    Historique des Audits
                </h3>
                
                <div class="text-center text-gray-500 dark:text-gray-400 py-8">
                    <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h6m-6 4h6"/>
                    </svg>
                    <p class="text-sm">L'historique des audits sera disponible dans une prochaine mise à jour.</p>
                    <p class="text-xs mt-1">Les audits seront automatiquement enregistrés pour un suivi dans le temps.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection