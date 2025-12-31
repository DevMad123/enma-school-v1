@extends('layouts.dashboard')

@section('title', 'Affectations Pédagogiques')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">📚 Affectations Pédagogiques</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">MODULE A4 - Gestion des affectations enseignants ↔ classes</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.assignments.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle affectation
                </a>
                <a href="{{ route('admin.assignments.schedule') }}" class="inline-flex items-center px-4 py-2 border border-cyan-300 dark:border-cyan-600 rounded-md shadow-sm text-sm font-medium text-cyan-700 dark:text-cyan-300 bg-white dark:bg-gray-800 hover:bg-cyan-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500 transition duration-150">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Planning
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto space-y-6">
    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">🔍 Filtres de recherche</h3>
        </div>
        <div class="p-6">
            <form method="GET" action="{{ route('admin.assignments.index') }}" class="space-y-4 md:space-y-0 md:grid md:grid-cols-7 md:gap-4">
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Année académique</label>
                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="academic_year_id" name="academic_year_id">
                        <option value="">Toutes</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="teacher_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enseignant</label>
                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="teacher_id" name="teacher_id">
                        <option value="">Tous</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}" {{ request('teacher_id') == $teacher->id ? 'selected' : '' }}>
                                {{ $teacher->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="class_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Classe</label>
                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="class_id" name="class_id">
                        <option value="">Toutes</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                {{ $class->level->name }} {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Matière</label>
                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="subject_id" name="subject_id">
                        <option value="">Toutes</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ request('subject_id') == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="assignment_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="assignment_type" name="assignment_type">
                        <option value="">Tous</option>
                        <option value="regular" {{ request('assignment_type') == 'regular' ? 'selected' : '' }}>Régulier</option>
                        <option value="substitute" {{ request('assignment_type') == 'substitute' ? 'selected' : '' }}>Remplaçant</option>
                        <option value="temporary" {{ request('assignment_type') == 'temporary' ? 'selected' : '' }}>Temporaire</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Rechercher
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-blue-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-blue-200">Total</h6>
                        <h3 class="text-2xl font-bold text-white">{{ $assignments->total() }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-green-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-green-200">Actives</h6>
                        <h3 class="text-2xl font-bold text-white">{{ \App\Models\TeacherAssignment::where('is_active', true)->count() }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-green-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-yellow-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-yellow-200">Temporaires</h6>
                        <h3 class="text-2xl font-bold text-white">{{ \App\Models\TeacherAssignment::where('assignment_type', 'temporary')->count() }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-yellow-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="bg-cyan-600 text-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex justify-between items-center">
                    <div>
                        <h6 class="text-sm font-medium text-cyan-200">Enseignants</h6>
                        <h3 class="text-2xl font-bold text-white">{{ \App\Models\TeacherAssignment::distinct('teacher_id')->count('teacher_id') }}</h3>
                    </div>
                    <svg class="h-8 w-8 text-cyan-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des affectations -->
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">Liste des affectations</h3>
            <div>
                <button type="button" class="inline-flex items-center px-3 py-2 border border-yellow-300 dark:border-yellow-600 rounded-md shadow-sm text-sm font-medium text-yellow-700 dark:text-yellow-300 bg-white dark:bg-gray-800 hover:bg-yellow-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-150" data-bs-toggle="modal" data-bs-target="#duplicateModal">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Dupliquer affectations
                </button>
            </div>
        </div>
        <div class="overflow-x-auto">
            @if($assignments->count() > 0)
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Enseignant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Classe</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Matière</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Année</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Charge</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($assignments as $assignment)
                        <tr class="{{ $assignment->is_active ? 'hover:bg-gray-50 dark:hover:bg-gray-700' : 'bg-gray-100 dark:bg-gray-600' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 flex-shrink-0">
                                        <div class="h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center">
                                            <svg class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->teacher->full_name }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-300">{{ $assignment->teacher->specialization ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->schoolClass->level->name }} {{ $assignment->schoolClass->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-300">{{ $assignment->schoolClass->students_count ?? 0 }} étudiants</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($assignment->subject)
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-cyan-100 text-cyan-800 dark:bg-cyan-900 dark:text-cyan-200">{{ $assignment->subject->name }}</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Non définie</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{{ $assignment->academicYear->name }}</span>
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
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($assignment->weekly_hours)
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $assignment->weekly_hours }}h</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-300">par semaine</div>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400 text-sm">Non définie</span>
                                @endif
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
                                    <form action="{{ route('admin.assignments.toggle-status', $assignment) }}" 
                                          method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                                class="text-{{ $assignment->is_active ? 'yellow' : 'green' }}-600 hover:text-{{ $assignment->is_active ? 'yellow' : 'green' }}-900 dark:text-{{ $assignment->is_active ? 'yellow' : 'green' }}-400 dark:hover:text-{{ $assignment->is_active ? 'yellow' : 'green' }}-300"
                                                title="{{ $assignment->is_active ? 'Désactiver' : 'Activer' }}">
                                            @if($assignment->is_active)
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M19 10a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <div class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Affichage de {{ $assignments->firstItem() }} à {{ $assignments->lastItem() }} 
                            sur {{ $assignments->total() }} résultats
                        </div>
                        <div>
                            {{ $assignments->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune affectation trouvée</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Aucune affectation ne correspond à vos critères de recherche.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.assignments.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Créer la première affectation
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de duplication -->
<div class="fixed inset-0 z-50 overflow-y-auto hidden" id="duplicateModal" aria-labelledby="duplicateModalLabel" aria-hidden="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>
        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="duplicateModalLabel">
                            Dupliquer des affectations
                        </h3>
                        <form action="{{ route('admin.assignments.duplicate') }}" method="POST" class="mt-4">
                            @csrf
                            <div class="space-y-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Dupliquer les affectations d'une année académique vers une autre.</p>
                                
                                <div>
                                    <label for="from_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Depuis l'année académique</label>
                                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="from_year_id" name="from_year_id" required>
                                        <option value="">Sélectionner...</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div>
                                    <label for="to_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Vers l'année académique</label>
                                    <select class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" id="to_year_id" name="to_year_id" required>
                                        <option value="">Sélectionner...</option>
                                        @foreach($academicYears as $year)
                                            <option value="{{ $year->id }}">{{ $year->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4">
                                    <div class="flex">
                                        <svg class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                                Attention : Les affectations existantes ne seront pas écrasées.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Dupliquer
                                </button>
                                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm" data-bs-dismiss="modal">
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection