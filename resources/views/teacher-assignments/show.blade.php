@extends('layouts.dashboard')

@section('title', 'Détails de l\'Affectation')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Détails de l'Affectation</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $teacherAssignment->teacher->full_name }} - {{ $teacherAssignment->schoolClass->level->name }} {{ $teacherAssignment->schoolClass->name }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('teacher-assignments.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour à la liste
                </a>
                <a href="{{ route('teacher-assignments.edit', $teacherAssignment) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="max-w-5xl mx-auto">
                    
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
                            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                                <a href="{{ route('teacher.assignments', $teacherAssignment->teacher_id) }}" 
                                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Affectations de l'enseignant
                                </a>
                                
                                <a href="{{ route('class.teachers', $teacherAssignment->class_id) }}" 
                                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    Enseignants de la classe
                                </a>

                                <!-- Action de suppression -->
                                <form method="POST" action="{{ route('teacher-assignments.destroy', $teacherAssignment) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center justify-center"
                                            onclick="return confirm('Supprimer définitivement cette affectation ? Cette action est irréversible.')">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        Supprimer l'affectation
                                    </button>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection