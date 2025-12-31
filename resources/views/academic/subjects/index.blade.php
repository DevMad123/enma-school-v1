@extends('layouts.dashboard')

@section('title', 'Gestion des Matières')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- En-tête avec boutons d'action -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Matières</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Gérez les matières enseignées, leurs coefficients et leur attribution aux niveaux
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('academic.subjects.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer une matière
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistiques rapides -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-100 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $subjects->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Matières</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v6a2 2 0 002 2h2m9-9h2a2 2 0 012 2v6a2 2 0 01-2 2h-2m-7-4v4m0-4H9m3-4v4m0 0V9a2 2 0 00-2-2h2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $levels->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Niveaux</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $subjects->avg('coefficient') ? round($subjects->avg('coefficient'), 1) : 0 }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Coef. moyen</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-orange-100 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        @php
                            $totalAssignments = 0;
                            foreach($subjects as $subject) {
                                $totalAssignments += $subject->levels->count();
                            }
                        @endphp
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalAssignments }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Attributions</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table interactive des matières -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Table des Matières et Coefficients</h3>
                <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-500 dark:text-gray-400">Filtre par cycle :</span>
                    <select id="cycleFilter" class="text-sm border-gray-300 dark:border-gray-600 rounded-md dark:bg-gray-700 dark:text-white">
                        <option value="">Tous les cycles</option>
                        @foreach($levels->groupBy('cycle.name') as $cycleName => $levelGroup)
                            <option value="{{ $cycleName }}">{{ $cycleName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            @if($subjects->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Matière
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Code
                                </th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Coefficient
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Niveaux d'enseignement
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($subjects as $subject)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 subject-row" data-cycles="{{ $subject->levels->pluck('cycle.name')->unique()->implode(',') }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                                <span class="text-white font-bold text-sm">
                                                    {{ substr($subject->code, 0, 2) }}
                                                </span>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $subject->name }}</p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $subject->levels->count() }} niveau(x)</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 py-1 text-xs font-mono font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200 rounded">
                                            {{ $subject->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <div class="flex justify-center">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-5 h-5 {{ $i <= $subject->coefficient ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            @endfor
                                            <span class="ml-2 text-sm font-medium text-gray-900 dark:text-white">{{ $subject->coefficient }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @forelse($subject->levels->groupBy('cycle.name') as $cycleName => $cyclelevels)
                                                <div class="mb-1">
                                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full mr-1">
                                                        {{ $cycleName }}
                                                    </span>
                                                    @foreach($cyclelevels as $level)
                                                        <span class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 rounded mr-1">
                                                            {{ $level->name }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @empty
                                                <span class="px-2 py-1 text-xs text-red-600 dark:text-red-400 italic">
                                                    Aucun niveau assigné
                                                </span>
                                            @endforelse
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end space-x-2">
                                            <button onclick="openEditModal({{ $subject->id }}, '{{ $subject->name }}', '{{ $subject->code }}', {{ $subject->coefficient }}, [{{ $subject->levels->pluck('id')->implode(',') }}])" 
                                                    class="text-indigo-600 hover:text-indigo-900 p-1" title="Modifier">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="viewSubjectDetails({{ $subject->id }})" 
                                                    class="text-green-600 hover:text-green-900 p-1" title="Voir détails">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            <button onclick="confirmDelete({{ $subject->id }}, '{{ $subject->name }}')" 
                                                    class="text-red-600 hover:text-red-900 p-1" title="Supprimer">
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
            @else
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune matière</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Commencez par créer votre première matière d'enseignement.
                    </p>
                    <div class="mt-6">
                        <button onclick="openCreateModal()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Créer une matière
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pour créer une matière -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <form action="{{ route('academic.subjects.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Créer une nouvelle matière</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ajoutez une matière avec son coefficient et ses niveaux d'enseignement.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="create_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de la matière</label>
                        <input type="text" id="create_name" name="name" required
                               placeholder="Ex: Mathématiques, Histoire-Géographie..."
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="create_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code matière</label>
                        <input type="text" id="create_code" name="code" required maxlength="10"
                               placeholder="Ex: MATH, HIST, FR..."
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white uppercase">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="create_coefficient" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coefficient</label>
                    <div class="flex items-center space-x-4">
                        <input type="range" id="create_coefficient" name="coefficient" min="1" max="10" value="2" 
                               class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700" 
                               oninput="updateCoefficientDisplay('create')">
                        <div class="flex items-center space-x-2">
                            <span id="create_coefficient_display" class="text-2xl font-bold text-blue-600">2</span>
                            <div class="flex" id="create_coefficient_stars">
                                <!-- Stars will be generated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Niveaux d'enseignement</label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Sélectionnez les niveaux où cette matière sera enseignée :</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        @foreach($levels->groupBy('cycle.name') as $cycleName => $cyclelevels)
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-1">
                                    {{ $cycleName }}
                                </h4>
                                @foreach($cyclelevels as $level)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded">
                                        <input type="checkbox" name="levels[]" value="{{ $level->id }}" 
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $level->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
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

<!-- Modal pour modifier une matière -->
<div id="editModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full sm:p-6">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Modifier la matière</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Modifiez les informations de la matière.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom de la matière</label>
                        <input type="text" id="edit_name" name="name" required
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                    </div>

                    <div>
                        <label for="edit_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Code matière</label>
                        <input type="text" id="edit_code" name="code" required maxlength="10"
                               class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white uppercase">
                    </div>
                </div>

                <div class="mb-6">
                    <label for="edit_coefficient" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Coefficient</label>
                    <div class="flex items-center space-x-4">
                        <input type="range" id="edit_coefficient" name="coefficient" min="1" max="10" value="2" 
                               class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700" 
                               oninput="updateCoefficientDisplay('edit')">
                        <div class="flex items-center space-x-2">
                            <span id="edit_coefficient_display" class="text-2xl font-bold text-blue-600">2</span>
                            <div class="flex" id="edit_coefficient_stars">
                                <!-- Stars will be generated by JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Niveaux d'enseignement</label>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Sélectionnez les niveaux où cette matière sera enseignée :</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                        @foreach($levels->groupBy('cycle.name') as $cycleName => $cyclelevels)
                            <div class="space-y-2">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-600 pb-1">
                                    {{ $cycleName }}
                                </h4>
                                @foreach($cyclelevels as $level)
                                    <label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 p-1 rounded">
                                        <input type="checkbox" name="levels[]" value="{{ $level->id }}" 
                                               id="edit_level_{{ $level->id }}"
                                               class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $level->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Supprimer la matière</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400" id="deleteMessage">
                            Êtes-vous sûr de vouloir supprimer cette matière ?
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
// Filtrage par cycle
document.getElementById('cycleFilter').addEventListener('change', function() {
    const selectedCycle = this.value.toLowerCase();
    const rows = document.querySelectorAll('.subject-row');
    
    rows.forEach(row => {
        const cycles = row.dataset.cycles.toLowerCase();
        if (!selectedCycle || cycles.includes(selectedCycle)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Gestion du coefficient avec slider et étoiles
function updateCoefficientDisplay(type) {
    const slider = document.getElementById(type + '_coefficient');
    const display = document.getElementById(type + '_coefficient_display');
    const starsContainer = document.getElementById(type + '_coefficient_stars');
    
    const value = parseInt(slider.value);
    display.textContent = value;
    
    // Générer les étoiles
    starsContainer.innerHTML = '';
    for (let i = 1; i <= 5; i++) {
        const star = document.createElement('svg');
        star.className = `w-4 h-4 ${i <= value ? 'text-yellow-400' : 'text-gray-300'}`;
        star.setAttribute('fill', 'currentColor');
        star.setAttribute('viewBox', '0 0 20 20');
        star.innerHTML = '<path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>';
        starsContainer.appendChild(star);
    }
}

// Initialiser les displays des coefficients
document.addEventListener('DOMContentLoaded', function() {
    updateCoefficientDisplay('create');
    updateCoefficientDisplay('edit');
});

// Gestion des modales
function openCreateModal() {
    // Réinitialiser le formulaire
    document.getElementById('create_name').value = '';
    document.getElementById('create_code').value = '';
    document.getElementById('create_coefficient').value = 2;
    updateCoefficientDisplay('create');
    
    // Décocher toutes les checkboxes
    document.querySelectorAll('#createModal input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openEditModal(subjectId, subjectName, subjectCode, coefficient, selectedLevels) {
    document.getElementById('editForm').action = `/academic/subjects/${subjectId}`;
    document.getElementById('edit_name').value = subjectName;
    document.getElementById('edit_code').value = subjectCode;
    document.getElementById('edit_coefficient').value = coefficient;
    updateCoefficientDisplay('edit');
    
    // Réinitialiser toutes les checkboxes
    document.querySelectorAll('#editModal input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    // Cocher les niveaux sélectionnés
    selectedLevels.forEach(levelId => {
        const checkbox = document.getElementById(`edit_level_${levelId}`);
        if (checkbox) {
            checkbox.checked = true;
        }
    });
    
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function confirmDelete(subjectId, subjectName) {
    document.getElementById('deleteForm').action = `/academic/subjects/${subjectId}`;
    document.getElementById('deleteMessage').textContent = `Êtes-vous sûr de vouloir supprimer la matière "${subjectName}" ? Cette action supprimera également toutes les attributions aux niveaux.`;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
    document.body.style.overflow = 'auto';
}

function viewSubjectDetails(subjectId) {
    // Placeholder pour la fonctionnalité de visualisation des détails
    alert('Fonctionnalité de visualisation des détails en cours de développement.');
}

// Conversion automatique du code en majuscules
document.getElementById('create_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

document.getElementById('edit_code').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});

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