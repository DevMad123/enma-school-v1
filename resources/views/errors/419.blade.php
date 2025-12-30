<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Session expirée - ENMA School</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900">
    <div class="min-h-full flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <!-- Logo/Icon -->
            <div class="flex justify-center">
                <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-full mb-8">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- Error Content -->
            <div class="text-center">
                <h1 class="text-6xl font-bold text-yellow-600 mb-4">419</h1>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Session expirée
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    Votre session a expiré pour des raisons de sécurité. 
                    Veuillez actualiser la page et essayer à nouveau.
                </p>

                <!-- Security Info -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-yellow-500 mr-3 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Mesure de sécurité
                            </h3>
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                Cette erreur se produit lorsque :<br>
                                • La session a expiré (inactivité prolongée)<br>
                                • Le token de sécurité CSRF n'est plus valide<br>
                                • La page est restée ouverte trop longtemps
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="location.reload()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Actualiser la page
                    </button>
                    
                    @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Tableau de bord
                    </a>
                    @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Se reconnecter
                    </a>
                    @endauth
                </div>

                <!-- Tips Section -->
                <div class="mt-12 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                    <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200 mb-2">
                        💡 Conseils pour éviter ce problème
                    </h3>
                    <ul class="text-xs text-blue-600 dark:text-blue-300 text-left space-y-1">
                        <li>• Évitez de garder plusieurs onglets ouverts avec la même application</li>
                        <li>• Sauvegardez régulièrement votre travail</li>
                        <li>• Déconnectez-vous proprement en fin de session</li>
                        <li>• Actualisez la page si vous restez inactif longtemps</li>
                    </ul>
                </div>

                <!-- Technical Info -->
                <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Code d'erreur : 419-{{ now()->format('YmdHis') }} | 
                        Horodatage : {{ now()->format('d/m/Y H:i:s') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-xs text-gray-400">
                &copy; {{ date('Y') }} ENMA School - Système de gestion scolaire
            </p>
        </div>
    </div>

    <script>
        // Auto-refresh countdown
        let countdown = 10;
        const refreshBtn = document.querySelector('[onclick="location.reload()"]');
        
        function updateCountdown() {
            if (countdown > 0) {
                refreshBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Actualiser (${countdown}s)
                `;
                countdown--;
                setTimeout(updateCountdown, 1000);
            } else {
                location.reload();
            }
        }
        
        // Démarrer le compte à rebours après 5 secondes
        setTimeout(updateCountdown, 5000);
    </script>
</body>
</html>