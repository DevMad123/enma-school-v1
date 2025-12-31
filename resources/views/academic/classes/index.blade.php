@extends('layouts.dashboard')

@section('title', 'Gestion des Classes')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec boutons d'action et filtres -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center space-y-4 lg:space-y-0">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Classes</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Gérez les classes par niveau et année académique (3e A, Tle S1, etc.)
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('academic.classes.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer une classe
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Année académique</label>
                    <select id="academic_year_id" name="academic_year_id" 
                            class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Toutes les années</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}" {{ request('academic_year_id') == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cycle_filter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Cycle</label>
                    <select id="cycle_filter" 
                            class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les cycles</option>
                        @foreach($cycles as $cycle)
                            <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Niveau</label>
                    <select id="level_id" name="level_id" 
                            class="w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les niveaux</option>
                        @foreach($levels as $level)
                            <option value="{{ $level->id }}" data-cycle="{{ $level->cycle_id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>
                                {{ $level->cycle->name }} - {{ $level->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        Filtrer
                    </button>
                </div>
            </form>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $classes->total() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Classes totales</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $classes->sum(function($class) { return $class->students->count(); }) }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Élèves inscrits</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m9-9h2a2 2 0 012 2v6a2 2 0 01-2 2h-2m-7-4v4m0-4H9m3-4v4m0 0V9a2 2 0 00-2-2h2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $classes->avg('capacity') ? round($classes->avg('capacity')) : 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Capacité moyenne</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        @php
                            $totalCapacity = $classes->sum('capacity');
                            $totalStudents = $classes->sum(function($class) { return $class->students->count(); });
                            $occupancyRate = $totalCapacity > 0 ? round(($totalStudents / $totalCapacity) * 100) : 0;
                        @endphp
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $occupancyRate }}%</p>
                        <p class="text-gray-600 dark:text-gray-400">Taux d'occupation</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des classes -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Liste des Classes
                    @if(request()->hasAny(['academic_year_id', 'level_id']))
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                            ({{ $classes->total() }} résultat{{ $classes->total() > 1 ? 's' : '' }} trouvé{{ $classes->total() > 1 ? 's' : '' }})
                        </span>
                    @endif
                </h3>
            </div>
            
            @if($classes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Classe
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Cycle / Niveau
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Année académique
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Effectif
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Capacité
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Taux
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($classes as $class)
                                @php
                                    $studentsCount = $class->students->count();
                                    $occupancyRate = $class->capacity > 0 ? round(($studentsCount / $class->capacity) * 100) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center mr-3">
                                                <span class="text-blue-600 dark:text-blue-300 font-semibold text-sm">
                                                    {{ substr($class->name, 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $class->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $class->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <p class="text-sm text-gray-900 dark:text-white">{{ $class->cycle->name }}</p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $class->level->name }}</p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                                            {{ $class->academicYear->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $studentsCount }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $class->capacity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($occupancyRate <= 50)
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 rounded-full">
                                                {{ $occupancyRate }}%
                                            </span>
                                        @elseif($occupancyRate <= 90)
                                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 rounded-full">
                                                {{ $occupancyRate }}%
                                            </span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 rounded-full">
                                                {{ $occupancyRate }}%
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button onclick="openEditModal({{ $class->id }}, '{{ $class->name }}', {{ $class->academic_year_id }}, {{ $class->level_id }}, {{ $class->capacity }})" 
                                                    class="text-indigo-600 hover:text-indigo-900 p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="confirmDelete({{ $class->id }}, '{{ $class->name }}', {{ $studentsCount }})" 
                                                    class="text-red-600 hover:text-red-900 p-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($classes->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                        {{ $classes->links() }}
                    </div>
                @endif
            @else
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune classe trouvée</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @if(request()->hasAny(['academic_year_id', 'level_id']))
                            Aucune classe ne correspond aux critères de filtrage.
                        @else
                            Commencez par créer votre première classe.
                        @endif
                    </p>
                    <div class="mt-6">
                        <button onclick="openCreateModal()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Créer une classe
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pour créer une classe -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form action="{{ route('academic.classes.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Créer une nouvelle classe</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ajoutez une classe pour un niveau et une année donnés.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="create_academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Année académique</label>
                        <select id="create_academic_year_id" name="academic_year_id" required 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner une année</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="create_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                        <select id="create_level_id" name="level_id" required 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner un niveau</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->cycle->name }} - {{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="create_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de la classe</label>
                        <input type="text" id="create_name" name="name" required
                               placeholder="Ex: A, B, S1, S2..."
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="create_capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacité maximale</label>
                        <input type="number" id="create_capacity" name="capacity" required min="1" max="100" value="30"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('createModal')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal pour modifier une classe -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Modifier la classe</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Modifiez les informations de la classe.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="edit_academic_year_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Année académique</label>
                        <select id="edit_academic_year_id" name="academic_year_id" required 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner une année</option>
                            @foreach($academicYears as $year)
                                <option value="{{ $year->id }}">{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_level_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Niveau</label>
                        <select id="edit_level_id" name="level_id" required 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner un niveau</option>
                            @foreach($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->cycle->name }} - {{ $level->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de la classe</label>
                        <input type="text" id="edit_name" name="name" required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="edit_capacity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Capacité maximale</label>
                        <input type="number" id="edit_capacity" name="capacity" required min="1" max="100"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editModal')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                        Modifier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <div class="sm:flex sm:items-start">
                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Supprimer la classe</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400" id="deleteMessage">
                            Êtes-vous sûr de vouloir supprimer cette classe ?
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                <form id="deleteForm" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Supprimer
                    </button>
                </form>
                <button type="button" onclick="closeModal('deleteModal')" 
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-600">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Gestion des filtres dynamiques
document.getElementById('cycle_filter').addEventListener('change', function() {
    const cycleId = this.value;
    const levelSelect = document.getElementById('level_id');
    const allOptions = Array.from(levelSelect.options);
    
    // Réinitialiser
    levelSelect.innerHTML = '<option value="">Tous les niveaux</option>';
    
    if (cycleId) {
        allOptions.forEach(option => {
            if (option.dataset.cycle == cycleId && option.value !== '') {
                levelSelect.appendChild(option);
            }
        });
    } else {
        allOptions.forEach(option => {
            if (option.value !== '' || option.textContent === 'Tous les niveaux') {
                levelSelect.appendChild(option);
            }
        });
    }
});

// Gestion des modales
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openEditModal(classId, className, academicYearId, levelId, capacity) {
    document.getElementById('editForm').action = `/academic/classes/${classId}`;
    document.getElementById('edit_name').value = className;
    document.getElementById('edit_academic_year_id').value = academicYearId;
    document.getElementById('edit_level_id').value = levelId;
    document.getElementById('edit_capacity').value = capacity;
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function confirmDelete(classId, className, studentsCount) {
    document.getElementById('deleteForm').action = `/academic/classes/${classId}`;
    let message = `Êtes-vous sûr de vouloir supprimer la classe "${className}" ?`;
    if (studentsCount > 0) {
        message += ` Cette classe contient ${studentsCount} élève(s) inscrit(s).`;
    }
    document.getElementById('deleteMessage').textContent = message;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer les modales en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    const modals = ['createModal', 'editModal', 'deleteModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (e.target === modal) {
            closeModal(modalId);
        }
    });
});

// Fermer les modales avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = ['createModal', 'editModal', 'deleteModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal.classList.contains('hidden')) {
                closeModal(modalId);
            }
        });
    }
});
</script>
@endpush