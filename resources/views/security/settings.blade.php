<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                Paramètres de Sécurité
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('security.audit') }}" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Audit de Sécurité
                </a>
                <a href="{{ route('security.activity-log') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h6m-6 4h6"/>
                    </svg>
                    Journal d'Activité
                </a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <form method="POST" action="{{ route('security.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Politique de mot de passe -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Politique de Mot de Passe
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Longueur minimale -->
                        <div>
                            <label for="password_min_length" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Longueur minimale
                            </label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="password_min_length" id="password_min_length" 
                                       value="{{ $securitySettings['password_min_length'] }}" min="6" max="128" required
                                       class="w-20 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <span class="text-sm text-gray-500 dark:text-gray-400">caractères</span>
                            </div>
                        </div>

                        <!-- Expiration -->
                        <div>
                            <label for="password_expiry_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Expiration du mot de passe
                            </label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="password_expiry_days" id="password_expiry_days" 
                                       value="{{ $securitySettings['password_expiry_days'] }}" min="0" max="365" required
                                       class="w-20 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <span class="text-sm text-gray-500 dark:text-gray-400">jours (0 = jamais)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Exigences de complexité -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Exigences de complexité</h4>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="password_require_uppercase" value="1" 
                                       {{ $securitySettings['password_require_uppercase'] ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Majuscules</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="password_require_lowercase" value="1" 
                                       {{ $securitySettings['password_require_lowercase'] ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Minuscules</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="password_require_numbers" value="1" 
                                       {{ $securitySettings['password_require_numbers'] ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Chiffres</span>
                            </label>

                            <label class="flex items-center">
                                <input type="checkbox" name="password_require_symbols" value="1" 
                                       {{ $securitySettings['password_require_symbols'] ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Symboles</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paramètres de session -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Gestion des Sessions
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Timeout de session -->
                        <div>
                            <label for="session_timeout_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Timeout de session
                            </label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="session_timeout_minutes" id="session_timeout_minutes" 
                                       value="{{ $securitySettings['session_timeout_minutes'] }}" min="5" max="1440" required
                                       class="w-20 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <span class="text-sm text-gray-500 dark:text-gray-400">minutes</span>
                            </div>
                        </div>

                        <!-- Tentatives de connexion -->
                        <div>
                            <label for="max_login_attempts" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Tentatives max
                            </label>
                            <input type="number" name="max_login_attempts" id="max_login_attempts" 
                                   value="{{ $securitySettings['max_login_attempts'] }}" min="1" max="20" required
                                   class="w-20 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                        </div>

                        <!-- Durée de verrouillage -->
                        <div>
                            <label for="lockout_duration_minutes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Verrouillage
                            </label>
                            <div class="flex items-center space-x-2">
                                <input type="number" name="lockout_duration_minutes" id="lockout_duration_minutes" 
                                       value="{{ $securitySettings['lockout_duration_minutes'] }}" min="1" max="1440" required
                                       class="w-20 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <span class="text-sm text-gray-500 dark:text-gray-400">minutes</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paramètres d'activité et notifications -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM9 12H4l5-5v5zM12 8V3"/>
                        </svg>
                        Activité et Notifications
                    </h3>
                    
                    <div class="space-y-4">
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_user_activity_log" value="1" 
                                   {{ $securitySettings['enable_user_activity_log'] ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Journal d'activité des utilisateurs</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Enregistrer toutes les actions des utilisateurs</p>
                            </div>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="enable_login_notifications" value="1" 
                                   {{ $securitySettings['enable_login_notifications'] ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Notifications de connexion</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Notifier les utilisateurs des nouvelles connexions</p>
                            </div>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="force_email_verification" value="1" 
                                   {{ $securitySettings['force_email_verification'] ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Vérification d'email obligatoire</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Forcer la vérification d'email pour tous les nouveaux comptes</p>
                            </div>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" name="require_password_change_on_first_login" value="1" 
                                   {{ $securitySettings['require_password_change_on_first_login'] ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Changement de mot de passe obligatoire</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Forcer le changement à la première connexion</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Sécurité avancée -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-6 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Sécurité Avancée
                    </h3>
                    
                    <div class="space-y-6">
                        <!-- Authentification à deux facteurs -->
                        <label class="flex items-center">
                            <input type="checkbox" name="enable_two_factor_auth" value="1" 
                                   {{ $securitySettings['enable_two_factor_auth'] ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <div class="ml-3">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Authentification à deux facteurs</span>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Activer l'A2F pour une sécurité renforcée</p>
                            </div>
                        </label>

                        <!-- Liste blanche IP -->
                        <div>
                            <label class="flex items-center mb-3">
                                <input type="checkbox" name="enable_ip_whitelist" value="1" 
                                       {{ $securitySettings['enable_ip_whitelist'] ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <div class="ml-3">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Liste blanche d'adresses IP</span>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Restreindre l'accès aux IP autorisées uniquement</p>
                                </div>
                            </label>
                            
                            <div class="ml-6">
                                <label for="allowed_ip_addresses" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Adresses IP autorisées (une par ligne)
                                </label>
                                <textarea name="allowed_ip_addresses" id="allowed_ip_addresses" rows="4"
                                          class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm"
                                          placeholder="192.168.1.0/24&#10;10.0.0.1&#10;203.0.113.0/24">{{ $securitySettings['allowed_ip_addresses'] }}</textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Formats supportés : adresses individuelles (192.168.1.1) ou plages CIDR (192.168.1.0/24)
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('users.index') }}" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-2 rounded-lg font-medium transition duration-150">
                    Annuler
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition duration-150">
                    <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>
</x-app-layout>