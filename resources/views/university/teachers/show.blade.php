@extends('layouts.dashboard')

@section('title', 'Détails de l\'enseignant universitaire')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    {{ $teacher->first_name }} {{ $teacher->last_name }}
                    <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($teacher->status == 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                        @elseif($teacher->status == 'inactive') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                        @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                        {{ ucfirst($teacher->status) }}
                    </span>
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Profil détaillé de l'enseignant universitaire</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('university.teachers.edit', $teacher) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('university.teachers.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
    <!-- Informations principales -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations personnelles -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="bg-blue-50 dark:bg-blue-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Informations personnelles
                </h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-y-4">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Nom complet</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $teacher->first_name }} {{ $teacher->last_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            <a href="mailto:{{ $teacher->user->email }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                {{ $teacher->user->email }}
                            </a>
                        </dd>
                    </div>
                    @if($teacher->phone)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Téléphone</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">
                            <a href="tel:{{ $teacher->phone }}" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-200">
                                {{ $teacher->phone }}
                            </a>
                        </dd>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Statut</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($teacher->status == 'active') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                @elseif($teacher->status == 'inactive') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                {{ ucfirst($teacher->status) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Créé le</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->created_at->format('d/m/Y à H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Modifié le</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->updated_at->format('d/m/Y à H:i') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Informations académiques -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="bg-indigo-50 dark:bg-indigo-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-indigo-900 dark:text-indigo-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Informations académiques
                </h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-y-4">
                    @if($teacher->ufr)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">UFR</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-medium">{{ $teacher->ufr->name }}</dd>
                    </div>
                    @endif
                    @if($teacher->department)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Département</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->department->name }}</dd>
                    </div>
                    @endif
                    @if($teacher->academic_rank)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Rang académique</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                @switch($teacher->academic_rank)
                                    @case('assistant')
                                        Assistant
                                        @break
                                    @case('maitre_assistant')
                                        Maître Assistant
                                        @break
                                    @case('maitre_de_conferences')
                                        Maître de Conférences
                                        @break
                                    @case('professeur')
                                        Professeur
                                        @break
                                    @case('professeur_titulaire')
                                        Professeur Titulaire
                                        @break
                                    @default
                                        {{ ucfirst($teacher->academic_rank) }}
                                @endswitch
                            </span>
                        </dd>
                    </div>
                    @endif
                    @if($teacher->specialization)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Spécialisation</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->specialization }}</dd>
                    </div>
                    @endif
                    @if($teacher->research_interests)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Recherche</dt>
                        <dd class="text-sm text-gray-500 dark:text-gray-400">
                            {{ Str::limit($teacher->research_interests, 100) }}
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <!-- Informations professionnelles et programmes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Informations professionnelles -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="bg-green-50 dark:bg-green-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-green-900 dark:text-green-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6M8 8v10a2 2 0 002 2h4a2 2 0 002-2V8M8 8V6a2 2 0 012-2h4a2 2 0 012 2v2M8 8h8"></path>
                    </svg>
                    Informations professionnelles
                </h3>
            </div>
            <div class="px-6 py-4">
                <dl class="grid grid-cols-1 gap-y-4">
                    @if($teacher->employee_id)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">N° employé</dt>
                        <dd class="text-sm text-gray-900 dark:text-white font-mono bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">{{ $teacher->employee_id }}</dd>
                    </div>
                    @endif
                    @if($teacher->hire_date)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Date d'embauche</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->hire_date->format('d/m/Y') }}</dd>
                    </div>
                    @endif
                    @if($teacher->office_location)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Bureau</dt>
                        <dd class="text-sm text-gray-900 dark:text-white">{{ $teacher->office_location }}</dd>
                    </div>
                    @endif
                    @if($teacher->salary)
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Salaire</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ number_format($teacher->salary, 0, ',', ' ') }} FCFA
                            </span>
                        </dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Programmes enseignés -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="bg-orange-50 dark:bg-orange-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-orange-900 dark:text-orange-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Programmes
                </h3>
            </div>
            <div class="px-6 py-4">
                @if($teacher->programs && $teacher->programs->count() > 0)
                    <div class="space-y-3">
                        @foreach($teacher->programs as $program)
                            <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $program->name }}</h4>
                                    @if($program->level)
                                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $program->level->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun programme</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aucun programme assigné à cet enseignant.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Qualifications et recherche -->
    @if($teacher->qualifications || $teacher->research_interests)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @if($teacher->qualifications)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="bg-purple-50 dark:bg-purple-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-purple-900 dark:text-purple-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                    </svg>
                    Qualifications
                </h3>
            </div>
            <div class="px-6 py-4">
                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{ $teacher->qualifications }}
                </div>
            </div>
        </div>
        @endif

        @if($teacher->research_interests)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="bg-pink-50 dark:bg-pink-900 px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-pink-900 dark:text-pink-100 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    Intérêts de recherche
                </h3>
            </div>
            <div class="px-6 py-4">
                <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line">
                    {{ $teacher->research_interests }}
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <!-- Actions -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
        <div class="px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex space-x-3">
                    <a href="{{ route('university.teachers.edit', $teacher) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Modifier
                    </a>
                    <button type="button" 
                            class="bg-{{ $teacher->status == 'active' ? 'red' : 'green' }}-500 hover:bg-{{ $teacher->status == 'active' ? 'red' : 'green' }}-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center"
                            onclick="toggleStatus({{ $teacher->id }})">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $teacher->status == 'active' ? 'M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z' : 'M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m-9 4h10a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2z' }}"></path>
                        </svg>
                        {{ $teacher->status == 'active' ? 'Désactiver' : 'Activer' }}
                    </button>
                </div>
                <div>
                    <button type="button" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center"
                            onclick="deleteTeacher({{ $teacher->id }})">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900">
                <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mt-4">Confirmer la suppression</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Êtes-vous sûr de vouloir supprimer cet enseignant ?
                </p>
                <p class="text-sm text-red-600 dark:text-red-400 mt-2">
                    <strong>Cette action est irréversible !</strong>
                </p>
            </div>
            <div class="items-center px-4 py-3 flex justify-center space-x-3">
                <button type="button" 
                        onclick="closeDeleteModal()" 
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150">
                    Annuler
                </button>
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleStatus(teacherId) {
    if (confirm('Êtes-vous sûr de vouloir changer le statut de cet enseignant ?')) {
        fetch(`/university/teachers/${teacherId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors du changement de statut');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors du changement de statut');
        });
    }
}

function deleteTeacher(teacherId) {
    const form = document.getElementById('deleteForm');
    form.action = `/university/teachers/${teacherId}`;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endpush
@endsection