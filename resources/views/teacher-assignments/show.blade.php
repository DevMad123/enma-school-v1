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
                        Détails de l'Affectation
                    </h2>
                    <a href="{{ route('teacher-assignments.index') }}" 
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
                            
                            <!-- Titre de l'affectation -->
                            <div class="mb-6">
                                <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-200">
                                    {{ $teacherAssignment->full_name }}
                                </h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                                    Affectation créée le {{ $teacherAssignment->created_at->format('d/m/Y à H:i') }}
                                </p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                
                                <!-- Informations sur l'enseignant -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        👨‍🏫 Informations Enseignant
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nom complet</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->teacher->full_name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->teacher->user->email }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Téléphone</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $teacherAssignment->teacher->phone ?: 'Non renseigné' }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Spécialisation</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $teacherAssignment->teacher->specialization ?: 'Non spécifiée' }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Statut</label>
                                            <span class="px-2 py-1 text-xs rounded-full {{ $teacherAssignment->teacher->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst($teacherAssignment->teacher->status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations sur la classe -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        🏫 Informations Classe
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Classe</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $teacherAssignment->schoolClass->level->name }} {{ $teacherAssignment->schoolClass->name }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Cycle</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->schoolClass->cycle->name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Niveau</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->schoolClass->level->name }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Capacité</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->schoolClass->capacity }} élèves</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Étudiants inscrits</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $teacherAssignment->schoolClass->enrolled_students_count }} élèves
                                            </p>
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
                                                {{ $teacherAssignment->academicYear->name }}
                                                @if($teacherAssignment->academicYear->is_active)
                                                    <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Actuelle</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Période</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                Du {{ $teacherAssignment->academicYear->start_date->format('d/m/Y') }}
                                                au {{ $teacherAssignment->academicYear->end_date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Nombre total d'affectations</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                {{ $teacherAssignment->academicYear->total_assignments }} affectations
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Informations sur l'affectation -->
                                <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                                        📋 Détails Affectation
                                    </h3>
                                    <div class="space-y-3">
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Date de création</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->created_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Dernière modification</label>
                                            <p class="text-gray-900 dark:text-gray-100">{{ $teacherAssignment->updated_at->format('d/m/Y à H:i') }}</p>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Matière spécifique</label>
                                            <p class="text-gray-900 dark:text-gray-100">
                                                <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-800 rounded-full">
                                                    À définir dans le module suivant
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Actions -->
                            <div class="mt-8 flex space-x-4">
                                <a href="{{ route('teacher-assignments.edit', $teacherAssignment) }}" 
                                   class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                    ✏️ Modifier l'affectation
                                </a>
                                
                                <a href="{{ route('teacher.assignments', $teacherAssignment->teacher_id) }}" 
                                   class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    👨‍🏫 Voir toutes les affectations de cet enseignant
                                </a>
                                
                                <a href="{{ route('class.teachers', $teacherAssignment->class_id) }}" 
                                   class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                    🏫 Voir tous les enseignants de cette classe
                                </a>
                            </div>

                            <!-- Action de suppression -->
                            <div class="mt-4">
                                <form method="POST" action="{{ route('teacher-assignments.destroy', $teacherAssignment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="bg-red-600 hover:bg-red-800 text-white font-bold py-2 px-4 rounded"
                                            onclick="return confirm('Supprimer définitivement cette affectation ? Cette action est irréversible.')">
                                        🗑️ Supprimer l'affectation
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