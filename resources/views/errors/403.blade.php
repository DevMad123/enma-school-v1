<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès refusé - ENMA School</title>
    
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
                <div class="flex items-center justify-center w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-full mb-8">
                    <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>

            <!-- Error Content -->
            <div class="text-center">
                <h1 class="text-6xl font-bold text-red-600 mb-4">403</h1>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-4">
                    Accès refusé
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-8 max-w-md mx-auto">
                    Vous n'avez pas les permissions nécessaires pour accéder à cette page. 
                    Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
                </p>

                <!-- Permission Info -->
                @if(auth()->check())
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4 mb-6">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="text-left">
                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                Information sur votre compte
                            </h3>
                            <p class="text-xs text-yellow-700 dark:text-yellow-300 mt-1">
                                Connecté en tant que : <strong>{{ auth()->user()->name }}</strong><br>
                                Rôle actuel : 
                                @if(auth()->user()->roles->isNotEmpty())
                                    <strong>{{ auth()->user()->roles->first()->name }}</strong>
                                @else
                                    <em>Aucun rôle assigné</em>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    @auth
                    <a href="{{ route('dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Retour au tableau de bord
                    </a>
                    @else
                    <a href="{{ route('login') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Se connecter
                    </a>
                    @endauth
                    
                    <button onclick="history.back()" 
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Page précédente
                    </button>
                </div>

                <!-- Help Section -->
                <div class="mt-12 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                    <h3 class="text-sm font-medium text-red-800 dark:text-red-200 mb-2">
                        Demander un accès
                    </h3>
                    <p class="text-xs text-red-600 dark:text-red-300 mb-3">
                        Si vous avez besoin d'accéder à cette ressource, contactez votre administrateur 
                        avec les informations suivantes :
                    </p>
                    <div class="text-xs text-red-600 dark:text-red-300 bg-red-100 dark:bg-red-800 p-2 rounded">
                        <div><strong>URL :</strong> {{ request()->fullUrl() }}</div>
                        <div><strong>Code :</strong> 403-{{ now()->format('YmdHis') }}</div>
                        @auth
                        <div><strong>Utilisateur :</strong> {{ auth()->user()->email }}</div>
                        @endauth
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
</body>
</html>