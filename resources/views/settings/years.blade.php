@extends('layouts.dashboard')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Années scolaires</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Gérez les années scolaires et leurs trimestres/semestres
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('settings.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
            <button type="button" 
                    onclick="openCreateYearModal()"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelle année
            </button>
        </div>
    </div>

    <!-- Liste des années scolaires -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Années scolaires configurées</h2>
        </div>

        @if($years->count() > 0)
            <div class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($years as $year)
                    <div class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                        {{ $year->name }}
                                    </h3>
                                    @if($year->is_current)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            Actuelle
                                        </span>
                                    @endif
                                    @if($year->is_archived)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400">
                                            Archivée
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1 flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span>Du {{ $year->start_date->format('d/m/Y') }} au {{ $year->end_date->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span>{{ $year->gradePeriods->count() }} période(s)</span>
                                </div>
                                
                                <!-- Périodes -->
                                @if($year->gradePeriods->count() > 0)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($year->gradePeriods->sortBy('order') as $period)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                {{ $period->name }}
                                                <span class="ml-1 text-xs opacity-75">
                                                    ({{ $period->start_date->format('d/m') }} - {{ $period->end_date->format('d/m') }})
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-3">
                                @if(!$year->is_current && !$year->is_archived)
                                    <button type="button" 
                                            onclick="setCurrentYear({{ $year->id }})"
                                            class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                @endif
                                
                                <form action="{{ route('settings.years.archive', $year) }}" 
                                      method="POST" 
                                      class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            title="{{ $year->is_archived ? 'Désarchiver' : 'Archiver' }}">
                                        @if($year->is_archived)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                                            </svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6m-6 0l6-6 2-2 4-4"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune année scolaire</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Commencez par créer votre première année scolaire.</p>
                <div class="mt-6">
                    <button type="button" 
                            onclick="openCreateYearModal()"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Créer une année scolaire
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal de création d'année scolaire -->
<div id="createYearModal" 
     class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex items-center justify-center p-4"
     x-show="showCreateModal" 
     x-data="{ showCreateModal: false }"
     @click.self="showCreateModal = false">
    
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full">
        <form action="{{ route('settings.years.store') }}" method="POST" class="space-y-6 p-6">
            @csrf
            
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Nouvelle année scolaire</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Créez une nouvelle année scolaire avec ses trimestres automatiques.
                </p>
            </div>

            <!-- Nom de l'année -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nom de l'année
                </label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       placeholder="Ex: 2024-2025"
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                       required>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de début
                    </label>
                    <input type="date" 
                           id="start_date" 
                           name="start_date" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           required>
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Date de fin
                    </label>
                    <input type="date" 
                           id="end_date" 
                           name="end_date" 
                           class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                           required>
                </div>
            </div>

            <!-- Année courante -->
            <div class="flex items-center">
                <input type="checkbox" 
                       id="is_current" 
                       name="is_current" 
                       value="1"
                       class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                <label for="is_current" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                    Définir comme année courante
                </label>
            </div>

            <!-- Boutons -->
            <div class="flex items-center justify-end space-x-3">
                <button type="button" 
                        onclick="closeCreateYearModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600">
                    Annuler
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Créer l'année
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateYearModal() {
    document.getElementById('createYearModal').classList.remove('hidden');
}

function closeCreateYearModal() {
    document.getElementById('createYearModal').classList.add('hidden');
}

function setCurrentYear(yearId) {
    if (confirm('Voulez-vous définir cette année comme année courante ?')) {
        // Créer un formulaire pour envoyer la requête
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/settings/years/${yearId}/set-current`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PATCH';
        
        form.appendChild(csrfToken);
        form.appendChild(method);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection