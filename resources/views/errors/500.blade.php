<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Erreur serveur - ENMA School</title>
    
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
                <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-orange-500 to-orange-600 rounded-full mb-8">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- Error Content -->
            <div class="text-center">
                <h1 class="text-6xl font-bold text-orange-600 mb-4">500</h1>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Erreur interne du serveur
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    Une erreur technique s'est produite. Nos équipes en ont été notifiées 
                    et travaillent à résoudre le problème.
                </p>

                @if(config('app.debug') && isset($exception))
                <!-- Debug Information (Only shown in debug mode) -->
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 mb-6 text-left">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                        Informations de débogage
                    </h3>
                    <div class="text-xs font-mono text-red-600 dark:text-red-300 space-y-1">
                        <div><strong>Erreur :</strong> {{ $exception->getMessage() }}</div>
                        <div><strong>Fichier :</strong> {{ $exception->getFile() }}</div>
                        <div><strong>Ligne :</strong> {{ $exception->getLine() }}</div>
                        @if(method_exists($exception, 'getCode') && $exception->getCode())
                        <div><strong>Code :</strong> {{ $exception->getCode() }}</div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Status Check -->
                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-500 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-orange-600 dark:text-orange-400">
                            Vérification de l'état du système...
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <button onclick="location.reload()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réessayer
                    </button>
                    
                    @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Tableau de bord
                    </a>
                    @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Accueil
                    </a>
                    @endauth
                </div>

                <!-- Report Section -->
                <div class="mt-12 p-4 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
                    <h3 class="text-sm font-medium text-gray-800 dark:text-gray-200 mb-2">
                        Signaler le problème
                    </h3>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-3">
                        Si le problème persiste, veuillez communiquer ces informations à l'équipe technique :
                    </p>
                    <div class="text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 p-2 rounded font-mono">
                        <div><strong>Horodatage :</strong> {{ now()->format('d/m/Y H:i:s') }}</div>
                        <div><strong>ID d'erreur :</strong> 500-{{ now()->format('YmdHis') }}-{{ Str::random(6) }}</div>
                        <div><strong>URL :</strong> {{ request()->fullUrl() }}</div>
                        @auth
                        <div><strong>Utilisateur :</strong> {{ auth()->user()->email }}</div>
                        @endauth
                        <div><strong>User Agent :</strong> {{ substr(request()->userAgent() ?? 'Unknown', 0, 50) }}{{ strlen(request()->userAgent() ?? '') > 50 ? '...' : '' }}</div>
                    </div>
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
        // Auto-refresh après 30 secondes
        setTimeout(function() {
            const refreshBtn = document.querySelector('[onclick="location.reload()"]');
            if (refreshBtn) {
                refreshBtn.innerHTML = '<svg class="w-4 h-4 mr-2 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>Actualisation automatique...';
                refreshBtn.disabled = true;
                setTimeout(() => location.reload(), 3000);
            }
        }, 30000);
    </script>
</body>
</html>