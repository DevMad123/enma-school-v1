@extends('layouts.dashboard')

@section('title', 'Journal d\'Activité')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Journal d'Activité</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Suivi des actions des utilisateurs et événements de sécurité</p>
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
                    Actualiser
                </button>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="mx-auto">
        <!-- Filtres -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <form method="GET" action="{{ route('security.activity-log') }}" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recherche</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}" 
                               placeholder="Utilisateur, action ou description..." 
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                    </div>
                    
                    <div class="md:w-48">
                        <label for="action_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type d'action</label>
                        <select name="action_type" id="action_type" class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                            <option value="">Tous les types</option>
                            <option value="login" {{ request('action_type') == 'login' ? 'selected' : '' }}>Connexion</option>
                            <option value="logout" {{ request('action_type') == 'logout' ? 'selected' : '' }}>Déconnexion</option>
                            <option value="create" {{ request('action_type') == 'create' ? 'selected' : '' }}>Création</option>
                            <option value="update" {{ request('action_type') == 'update' ? 'selected' : '' }}>Modification</option>
                            <option value="delete" {{ request('action_type') == 'delete' ? 'selected' : '' }}>Suppression</option>
                        </select>
                    </div>
                    
                    <div class="md:w-40">
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Du</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                    </div>
                    
                    <div class="md:w-40">
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Au</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                               class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                    </div>
                    
                    <div class="md:w-32">
                        <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition duration-150">
                            Filtrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Contenu principal -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="text-center py-16">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h6m-6 4h6"/>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Journal d'Activité - En Développement
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                        Le système de journal d'activité sera disponible dans une prochaine mise à jour. 
                        Cette fonctionnalité permettra de suivre toutes les actions des utilisateurs pour 
                        améliorer la sécurité et la traçabilité du système.
                    </p>
                    
                    <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg max-w-md mx-auto">
                        <h4 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                            Fonctionnalités prévues :
                        </h4>
                        <ul class="text-xs text-blue-600 dark:text-blue-300 text-left space-y-1">
                            <li>• Historique des connexions/déconnexions</li>
                            <li>• Traçage des modifications de données</li>
                            <li>• Détection des activités suspectes</li>
                            <li>• Export et archivage des logs</li>
                            <li>• Alertes de sécurité automatisées</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Instructions pour activer le logging -->
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                    Configuration du Journal d'Activité
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Journal activé
                        </h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Le journal d'activité des utilisateurs est actuellement activé dans les paramètres de sécurité.
                        </p>
                    </div>

                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            Modèle de données requis
                        </h4>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                            Un modèle UserActivity doit être créé pour stocker les événements d'activité.
                        </p>
                    </div>
                </div>

                <div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">
                        Pour développeurs - Étapes d'implémentation :
                    </h4>
                    <ol class="text-xs text-gray-600 dark:text-gray-400 space-y-1 list-decimal list-inside">
                        <li>Créer le modèle UserActivity et la migration associée</li>
                        <li>Implémenter des observers/listeners pour capturer les événements</li>
                        <li>Ajouter les middlewares de traçabilité</li>
                        <li>Compléter la logique du contrôleur SecurityController::activityLog()</li>
                        <li>Intégrer les notifications de sécurité</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection