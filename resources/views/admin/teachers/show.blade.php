@extends('layouts.dashboard')

@section('title', 'Détails Enseignant - ' . $teacher->full_name)

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">👨‍🏫 {{ $teacher->full_name }}</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $teacher->specialization ?? 'Spécialisation non définie' }}</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.teachers.edit', $teacher) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifier
                </a>
                <a href="{{ route('admin.teachers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-blue-200">Affectations</h6>
                        <h3 class="text-2xl font-bold text-white">{{ $stats['active_assignments'] }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-cyan-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-cyan-200">Classes</h6>
                        <h3 class="text-2xl font-bold text-white">{{ $stats['classes_count'] }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-green-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-green-200">Matières</h6>
                        <h3 class="text-2xl font-bold text-white">{{ $stats['subjects_count'] }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-green-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-yellow-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-yellow-200">Charge horaire</h6>
                        <h3 class="text-2xl font-bold text-white">{{ $stats['total_weekly_hours'] }}h</h3>
                        <small class="text-yellow-200">par semaine</small>
                    </div>
                    <svg class="h-8 w-8 text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="space-y-6">
            <!-- Informations personnelles -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h5 class="text-lg font-medium text-gray-900 dark:text-white">👤 Informations personnelles</h5>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom complet</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $teacher->full_name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                        <p class="text-sm">
                            <a href="mailto:{{ $teacher->user->email }}" class="text-blue-600 hover:text-blue-500">{{ $teacher->user->email }}</a>
                        </p>
                    </div>
                    
                    @if($teacher->phone)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Téléphone</label>
                        <p class="text-sm">
                            <a href="tel:{{ $teacher->phone }}" class="text-blue-600 hover:text-blue-500">{{ $teacher->phone }}</a>
                        </p>
                    </div>
                    @endif
                    
                    @if($teacher->employee_id)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Numéro d'employé</label>
                        <p class="text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $teacher->employee_id }}</span>
                        </p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">École</label>
                        <p class="text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200">{{ $teacher->school->name ?? 'Non assigné' }}</span>
                        </p>
                    </div>
                    
                    @if($teacher->hire_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date d'embauche</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $teacher->hire_date->format('d/m/Y') }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $teacher->hire_date->diffForHumans() }}
                        </p>
                    </div>
                    @endif
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Statut</label>
                        <p class="text-sm">
                            @switch($teacher->status)
                                @case('active')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Actif</span>
                                    @break
                                @case('inactive')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Inactif</span>
                                    @break
                                @case('retired')
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Retraité</span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                </div>
            </div>

            <!-- Informations professionnelles -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h5 class="text-lg font-medium text-gray-900 dark:text-white">🎓 Informations professionnelles</h5>
                </div>
                <div class="p-6 space-y-4">
                    @if($teacher->specialization)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Spécialisation</label>
                        <p class="text-sm">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">{{ $teacher->specialization }}</span>
                        </p>
                    </div>
                    @endif
                    
                    @if($teacher->teaching_subjects && count($teacher->teaching_subjects) > 0)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Matières enseignées</label>
                        <div class="flex flex-wrap gap-1">
                            @foreach($teacher->teaching_subjects as $subject)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200">{{ $subject }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    
                    @if($teacher->qualifications)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Qualifications</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $teacher->qualifications }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h5 class="text-lg font-medium text-gray-900 dark:text-white">⚡ Actions rapides</h5>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <a href="{{ route('admin.assignments.create') }}?teacher_id={{ $teacher->id }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Nouvelle affectation
                        </a>
                        <form action="{{ route('admin.teachers.toggle-status', $teacher) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" 
                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-{{ $teacher->status === 'active' ? 'yellow' : 'green' }}-300 rounded-md shadow-sm text-sm font-medium text-{{ $teacher->status === 'active' ? 'yellow' : 'green' }}-700 bg-white hover:bg-{{ $teacher->status === 'active' ? 'yellow' : 'green' }}-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-{{ $teacher->status === 'active' ? 'yellow' : 'green' }}-500 transition duration-150">
                                @if($teacher->status === 'active')
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M19 10a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                                {{ $teacher->status === 'active' ? 'Désactiver' : 'Activer' }}
                            </button>
                        </form>
                        <a href="{{ route('admin.teachers.edit', $teacher) }}" class="w-full inline-flex justify-center items-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier les informations
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-2 space-y-6">
            <!-- Affectations actuelles -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <h5 class="text-lg font-medium text-gray-900 dark:text-white">📚 Affectations pédagogiques</h5>
                    <a href="{{ route('admin.assignments.create') }}?teacher_id={{ $teacher->id }}" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nouvelle affectation
                    </a>
                </div>
                <div class="p-6">
                    @if($teacher->assignments->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Année académique</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Classe</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Matière</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Charge</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($teacher->assignments as $assignment)
                                    <tr class="{{ $assignment->is_active ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-gray-100 dark:bg-gray-600' }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                                {{ $assignment->academicYear->name }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->schoolClass->level->name }} {{ $assignment->schoolClass->name }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ $assignment->subject->name ?? 'Non définie' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @switch($assignment->assignment_type)
                                                @case('regular')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">Régulier</span>
                                                    @break
                                                @case('substitute')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Remplaçant</span>
                                                    @break
                                                @case('temporary')
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200">Temporaire</span>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $assignment->weekly_hours ? $assignment->weekly_hours . 'h/sem' : 'Non définie' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($assignment->is_active)
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Actif</span>
                                            @else
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">Inactif</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                                   class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Voir détails">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                                <a href="{{ route('admin.assignments.edit', $assignment) }}" 
                                                   class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300" title="Modifier">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                            </svg>
                            <h6 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune affectation</h6>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cet enseignant n'a pas encore d'affectation pédagogique.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.assignments.create') }}?teacher_id={{ $teacher->id }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Créer une affectation
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historique des modifications -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h5 class="text-lg font-medium text-gray-900 dark:text-white">📝 Informations système</h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium">Créé le :</span><br>
                                {{ $teacher->created_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span class="font-medium">Dernière modification :</span><br>
                                {{ $teacher->updated_at->format('d/m/Y à H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection