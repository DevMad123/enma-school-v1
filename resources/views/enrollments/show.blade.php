<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center">
                    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Détails de l'Inscription
                    </h2>
                    <a href="{{ route('enrollments.index') }}" 
                       class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Retour à la liste
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <div class="py-12">
                <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            
                            <!-- Statut de l'inscription -->
                            <div class="mb-6">
                                @switch($enrollment->status)
                                    @case('active')
                                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            📚 Inscription Active
                                        </span>
                                        @break
                                    @case('completed')
                                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            ✅ Inscription Terminée
                                        </span>
                                        @break
                                    @case('cancelled')
                                        <span class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            ❌ Inscription Annulée
                                        </span>
                                        @break
                                @endswitch
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <!-- Informations sur l'étudiant -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        👤 Informations Étudiant
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nom complet</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->student->full_name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->student->user->email }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Date de naissance</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $enrollment->student->date_of_birth ? $enrollment->student->date_of_birth->format('d/m/Y') : 'Non renseignée' }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Genre</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $enrollment->student->gender === 'male' ? 'Masculin' : ($enrollment->student->gender === 'female' ? 'Féminin' : 'Non spécifié') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations sur l'inscription -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        📝 Détails de l'Inscription
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Date d'inscription</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->enrollment_date->format('d/m/Y') }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Durée</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->duration_in_days }} jours</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Créée le</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->created_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Dernière modification</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->updated_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations sur l'année académique -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        📅 Année Académique
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nom</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $enrollment->academicYear->name }}
                                                @if($enrollment->academicYear->is_active)
                                                    <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Actuelle</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Période</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                Du {{ $enrollment->academicYear->start_date->format('d/m/Y') }}
                                                au {{ $enrollment->academicYear->end_date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations sur la classe -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        🏫 Classe Assignée
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Classe</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->schoolClass->name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Niveau</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->schoolClass->level->name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Capacité</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $enrollment->schoolClass->students_count }}/{{ $enrollment->schoolClass->capacity }} étudiants
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nom complet</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $enrollment->schoolClass->full_name }}</p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Actions -->
                            @if($enrollment->status === 'active')
                                <div class="mt-8 flex space-x-4">
                                    <form method="POST" action="{{ route('enrollments.complete', $enrollment) }}">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                                onclick="return confirm('Marquer cette inscription comme terminée ?')">
                                            ✅ Marquer comme terminée
                                        </button>
                                    </form>
                                    
                                    <form method="POST" action="{{ route('enrollments.cancel', $enrollment) }}">
                                        @csrf
                                        <button type="submit" 
                                                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                                onclick="return confirm('Annuler cette inscription ? L\'étudiant sera retiré de la classe.')">
                                            ❌ Annuler l'inscription
                                        </button>
                                    </form>
                                </div>
                            @endif

                            <!-- Action de suppression (pour tous les statuts) -->
                            <div class="mt-4">
                                <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-gray-600 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded"
                                            onclick="return confirm('Supprimer définitivement cette inscription ? Cette action est irréversible.')">
                                        🗑️ Supprimer définitivement
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>