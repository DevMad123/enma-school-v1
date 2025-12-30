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
                        Gestion des Inscriptions
                    </h2>
                    <a href="{{ route('enrollments.create') }}" 
                       class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Nouvelle Inscription
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main>
            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            
                            @if (session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Statistiques rapides -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">Inscriptions Actives</h3>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ $enrollments->where('status', 'active')->count() }}
                                    </p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Inscriptions Terminées</h3>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ $enrollments->where('status', 'completed')->count() }}
                                    </p>
                                </div>
                                <div class="bg-red-100 dark:bg-red-900 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-red-800 dark:text-red-200">Inscriptions Annulées</h3>
                                    <p class="text-2xl font-bold text-red-600 dark:text-red-400">
                                        {{ $enrollments->where('status', 'cancelled')->count() }}
                                    </p>
                                </div>
                            </div>

                            <!-- Table des inscriptions -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Étudiant
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Classe
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Année Académique
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Date d'inscription
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Statut
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse ($enrollments as $enrollment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $enrollment->student->full_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $enrollment->student->user->email }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $enrollment->schoolClass->full_name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $enrollment->academicYear->name }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $enrollment->enrollment_date->format('d/m/Y') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @switch($enrollment->status)
                                                        @case('active')
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                Active
                                                            </span>
                                                            @break
                                                        @case('completed')
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                Terminée
                                                            </span>
                                                            @break
                                                        @case('cancelled')
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                                Annulée
                                                            </span>
                                                            @break
                                                    @endswitch
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <a href="{{ route('enrollments.show', $enrollment) }}" 
                                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600">
                                                        Voir
                                                    </a>
                                                    
                                                    @if($enrollment->status === 'active')
                                                        <form method="POST" action="{{ route('enrollments.complete', $enrollment) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-600"
                                                                    onclick="return confirm('Marquer comme terminée ?')">
                                                                Terminer
                                                            </button>
                                                        </form>
                                                        
                                                        <form method="POST" action="{{ route('enrollments.cancel', $enrollment) }}" class="inline">
                                                            @csrf
                                                            <button type="submit" 
                                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600"
                                                                    onclick="return confirm('Annuler cette inscription ?')">
                                                                Annuler
                                                            </button>
                                                        </form>
                                                    @endif

                                                    <form method="POST" action="{{ route('enrollments.destroy', $enrollment) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600"
                                                                onclick="return confirm('Supprimer définitivement ?')">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                    Aucune inscription trouvée.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                {{ $enrollments->links() }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>