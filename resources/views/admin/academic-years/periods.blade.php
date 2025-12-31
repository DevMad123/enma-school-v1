@extends('layouts.dashboard')

@section('title', 'Gestion des Périodes - ' . $academicYear->name)

@push('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion des Périodes</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $academicYear->name }} - {{ $academicYear->school ? $academicYear->school->name : 'École non définie' }}
                </p>
            </div>
            <div class="flex space-x-3">
                @if($academicYear->academicPeriods->count() === 0 && $academicYear->school)
                    <button onclick="generatePeriods()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Générer les Périodes
                    </button>
                @endif
                <a href="{{ route('admin.academic-years.show', $academicYear) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Voir l'Année
                </a>
                <a href="{{ route('admin.academic-years.index', ['school_id' => $academicYear->school_id]) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Retour
                </a>
            </div>
        </div>
    </div>
</div>

<div class="mx-auto px-4 sm:px-6 lg:px-8">

    @if($academicYear->school)
        <!-- Informations sur l'école -->
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <p class="text-blue-800 dark:text-blue-200">
                    <strong>Système académique :</strong> {{ ucfirst($academicYear->school->academic_system) }}
                    - {{ $academicYear->school->academic_system === 'trimestre' ? '3 trimestres' : '2 semestres' }} attendus
                </p>
            </div>
        </div>
    @else
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <p class="text-red-800 dark:text-red-200">
                    <strong>Attention :</strong> Cette année académique n'est associée à aucune école. 
                    Veuillez d'abord assigner une école pour pouvoir gérer les périodes.
                </p>
            </div>
        </div>
    @endif

    @if($academicYear->academicPeriods->count() > 0)
        <!-- Liste des périodes existantes -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Périodes définies ({{ $academicYear->academicPeriods->count() }})
                </h3>
                
                <div class="space-y-4">
                    @foreach($academicYear->academicPeriods->sortBy('order') as $period)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center mb-2">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                            {{ $period->name }}
                                        </h4>
                                        <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $period->type === 'trimestre' ? 'bg-blue-100 text-blue-800 dark:bg-blue-800 dark:text-blue-100' : 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100' }}">
                                            {{ ucfirst($period->type) }} {{ $period->order }}
                                        </span>
                                        @if($period->is_active)
                                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
                                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                Active
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Dates :</span>
                                            <div class="text-gray-900 dark:text-white">
                                                {{ $period->start_date->format('d/m/Y') }} - {{ $period->end_date->format('d/m/Y') }}
                                            </div>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Durée :</span>
                                            <div class="text-gray-900 dark:text-white">
                                                {{ $period->start_date->diffInDays($period->end_date) + 1 }} jours
                                            </div>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-500 dark:text-gray-400">Statut :</span>
                                            <div class="text-gray-900 dark:text-white">
                                                @php
                                                    $now = now();
                                                @endphp
                                                @if($now < $period->start_date)
                                                    <span class="text-blue-600">À venir</span>
                                                @elseif($now >= $period->start_date && $now <= $period->end_date)
                                                    <span class="text-green-600">En cours</span>
                                                @else
                                                    <span class="text-gray-600">Terminée</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Barre de progression -->
                                    @php
                                        $total = $period->start_date->diffInDays($period->end_date) + 1;
                                        $elapsed = 0;
                                        $progress = 0;
                                        
                                        if ($now >= $period->start_date && $now <= $period->end_date) {
                                            $elapsed = $period->start_date->diffInDays($now) + 1;
                                            $progress = min(($elapsed / $total) * 100, 100);
                                        } elseif ($now > $period->end_date) {
                                            $progress = 100;
                                        }
                                    @endphp
                                    
                                    <div class="mt-3">
                                        <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1">
                                            <span>Progression</span>
                                            <span>{{ round($progress) }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ml-4 flex flex-col space-y-2">
                                    @if(!$period->is_active)
                                        <form action="{{ route('admin.academic-periods.toggle-active', $period) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300 text-sm font-medium"
                                                    onclick="return confirm('Activer cette période ?')">
                                                Activer
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.academic-periods.toggle-active', $period) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" 
                                                    class="text-orange-600 hover:text-orange-800 dark:text-orange-400 dark:hover:text-orange-300 text-sm font-medium"
                                                    onclick="return confirm('Désactiver cette période ?')">
                                                Désactiver
                                            </button>
                                        </form>
                                    @endif
                                    
                                    <button type="button" 
                                            onclick="editPeriod({{ $period->id }}, '{{ $period->name }}', '{{ $period->start_date->format('Y-m-d') }}', '{{ $period->end_date->format('Y-m-d') }}', {{ $period->order }})"
                                            class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium">
                                        Modifier
                                    </button>
                                    
                                    <form action="{{ route('admin.academic-periods.destroy', $period) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium"
                                                onclick="return confirm('Supprimer cette période ? Cette action est irréversible.')">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Formulaire d'ajout d'une nouvelle période -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">
                    Ajouter une nouvelle période
                </h3>
                
                <form action="{{ route('admin.academic-periods.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="new_period_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nom <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" id="new_period_name" required
                                   placeholder="ex: Trimestre 4"
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="new_period_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date début <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="start_date" id="new_period_start_date" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="new_period_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Date fin <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="end_date" id="new_period_end_date" required
                                   class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        
                        <div>
                            <label for="new_period_order" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Ordre <span class="text-red-500">*</span>
                            </label>
                            <select name="order" id="new_period_order" required
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                @for($i = 1; $i <= 6; $i++)
                                    <option value="{{ $i }}" {{ $i == $academicYear->academicPeriods->count() + 1 ? 'selected' : '' }}>
                                        {{ $i }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end">
                        <button type="submit" 
                                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Ajouter la période
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
    @else
        <!-- État vide - aucune période -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Aucune période définie</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Cette année académique n'a pas encore de périodes définies.
                </p>
                
                @if($academicYear->school)
                    <div class="mt-6">
                        <button onclick="generatePeriods()" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Générer automatiquement les {{ $academicYear->school->academic_system === 'trimestre' ? 'trimestres' : 'semestres' }}
                        </button>
                    </div>
                    
                    <div class="mt-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            ou créer manuellement une période ci-dessous
                        </p>
                    </div>
                    
                    <!-- Formulaire pour créer manuellement -->
                    <div class="mt-6 max-w-lg mx-auto">
                        <form action="{{ route('admin.academic-periods.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <input type="hidden" name="academic_year_id" value="{{ $academicYear->id }}">
                            
                            <div>
                                <input type="text" name="name" placeholder="Nom de la période" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <input type="date" name="start_date" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <input type="date" name="end_date" required
                                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            
                            <button type="submit" 
                                    class="w-full px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700">
                                Créer cette période
                            </button>
                        </form>
                    </div>
                @else
                    <div class="mt-6">
                        <p class="text-sm text-red-600 dark:text-red-400">
                            Veuillez d'abord assigner une école à cette année académique.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('admin.academic-years.edit', $academicYear) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                Modifier l'année académique
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>

<!-- Modal de modification (simplifié) -->
<div id="edit-period-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden" style="z-index: 50;">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Modifier la période</h3>
            
            <form action="#" method="POST" id="edit-period-form" class="space-y-4">
                @csrf
                @method('PUT')
                
                <input type="text" id="edit-period-name" placeholder="Nom" required
                       class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                
                <div class="grid grid-cols-2 gap-4">
                    <input type="date" id="edit-period-start" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <input type="date" id="edit-period-end" required
                           class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                </div>
                
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeEditModal()" 
                            class="px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-600 rounded-lg">
                        Annuler
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function generatePeriods() {
    if (confirm('Générer automatiquement les périodes selon le système académique de l\'école ?')) {
        // Ici, vous pourrez ajouter l'appel AJAX vers la route de génération automatique
        fetch('{{ route('admin.academic-years.generate-periods', $academicYear) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la génération des périodes');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Erreur lors de la génération des périodes');
        });
    }
}

function editPeriod(id, name, startDate, endDate, order) {
    document.getElementById('edit-period-name').value = name;
    document.getElementById('edit-period-start').value = startDate;
    document.getElementById('edit-period-end').value = endDate;
    
    // Update form action
    document.getElementById('edit-period-form').action = `{{ url('/admin/academic-periods') }}/${id}`;
    
    // Show modal
    document.getElementById('edit-period-modal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-period-modal').classList.add('hidden');
}

// Auto-hide modal when clicking outside
document.getElementById('edit-period-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>
@endpush