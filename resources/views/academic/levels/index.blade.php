@extends('layouts.dashboard')

@section('title', 'Gestion des Niveaux')

@section('content')
<div class="py-6">
    <div class="mx-auto space-y-6">
        <!-- Information contextuelle sur le type d'établissement -->
        @if(isset($school))
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg border border-blue-200 dark:border-gray-600 p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <div class="flex items-center">
                        <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                            {{ $school->name }} 
                        </h4>
                        <span class="ml-2 px-3 py-1 text-xs font-medium rounded-full {{ $isUniversity ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' }}">
                            {{ $school->getTypeLabel() }}
                        </span>
                    </div>
                    @if($isPreUniversity && !empty($educationalLevels))
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            <strong>Niveaux gérés :</strong> {{ implode(', ', $educationalLevels) }}
                        </p>
                    @endif
                    @if($isUniversity)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                            <strong>Structure :</strong> Organisation universitaire avec UFR, départements et programmes
                        </p>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- En-tête avec boutons d'action -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Niveaux</h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Gérez les niveaux d'étude par cycle (CP1, 6e, Tle, etc.)
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('academic.levels.create') }}" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Créer un niveau
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
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
                    <div class="p-3 bg-green-100 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $levels->sum(function($level) { return $level->classes->count(); }) }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Classes</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-100 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $levels->sum(function($level) { return $level->subjects->count(); }) }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Matières</p>
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
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $cycles->count() }}</p>
                        <p class="text-gray-600 dark:text-gray-400">Cycles</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Liste des niveaux par cycle -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">Liste des Niveaux</h3>
            </div>
            
            @if($levels->count() > 0)
                @php
                    $levelsByCycle = $levels->groupBy('cycle.name');
                @endphp
                
                <div class="p-6 space-y-8">
                    @foreach($levelsByCycle as $cycleName => $cyclevels)
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center">
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm mr-3">
                                    {{ $cycleName }}
                                </span>
                                ({{ $cyclevels->count() }} niveau{{ $cyclevels->count() > 1 ? 'x' : '' }})
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($cyclevels as $level)
                                    <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:shadow-md transition-shadow">
                                        <div class="flex justify-between items-start mb-3">
                                            <h5 class="text-lg font-medium text-gray-900 dark:text-white">{{ $level->name }}</h5>
                                            <div class="flex space-x-2">
                                                <button onclick="openEditModal({{ $level->id }}, '{{ $level->name }}', {{ $level->cycle_id }})" 
                                                        class="text-indigo-600 hover:text-indigo-900 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </button>
                                                <button onclick="confirmDelete({{ $level->id }}, '{{ $level->name }}')" 
                                                        class="text-red-600 hover:text-red-900 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                            <div class="flex justify-between">
                                                <span>Classes :</span>
                                                <span class="font-medium">{{ $level->classes->count() }}</span>
                                            </div>
                                            <div class="flex justify-between">
                                                <span>Matières :</span>
                                                <span class="font-medium">{{ $level->subjects->count() }}</span>
                                            </div>
                                        </div>

                                        @if($level->classes->count() > 0)
                                            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-600">
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($level->classes->take(3) as $class)
                                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded">
                                                            {{ $class->name }}
                                                        </span>
                                                    @endforeach
                                                    @if($level->classes->count() > 3)
                                                        <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs rounded">
                                                            +{{ $level->classes->count() - 3 }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucun niveau</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Commencez par créer votre premier niveau d'étude.
                    </p>
                    <div class="mt-6">
                        <button onclick="openCreateModal()" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Créer un niveau
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal pour créer un niveau -->
<div id="createModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
            <form action="{{ route('academic.levels.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Créer un nouveau niveau</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Ajoutez un niveau d'étude à un cycle existant.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="cycle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cycle</label>
                        <select id="cycle_id" name="cycle_id" required 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner un cycle</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du niveau</label>
                        <input type="text" id="name" name="name" required
                               placeholder="Ex: CP1, 6e, Tle..."
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

<!-- Modal pour modifier un niveau -->
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
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Modifier le niveau</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Modifiez les informations du niveau.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="edit_cycle_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cycle</label>
                        <select id="edit_cycle_id" name="cycle_id" required 
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white">
                            <option value="">Sélectionner un cycle</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}">{{ $cycle->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nom du niveau</label>
                        <input type="text" id="edit_name" name="name" required
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
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Supprimer le niveau</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400" id="deleteMessage">
                            Êtes-vous sûr de vouloir supprimer ce niveau ?
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
// Gestion des modales
function openCreateModal() {
    document.getElementById('createModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function openEditModal(levelId, levelName, cycleId) {
    document.getElementById('editForm').action = `/academic/levels/${levelId}`;
    document.getElementById('edit_name').value = levelName;
    document.getElementById('edit_cycle_id').value = cycleId;
    document.getElementById('editModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function confirmDelete(levelId, levelName) {
    document.getElementById('deleteForm').action = `/academic/levels/${levelId}`;
    document.getElementById('deleteMessage').textContent = `Êtes-vous sûr de vouloir supprimer le niveau "${levelName}" ?`;
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