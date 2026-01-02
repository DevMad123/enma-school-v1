@extends('layouts.dashboard')

@section('title', 'Gestion des Enseignants Universitaires')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">👨‍🏫 Enseignants Universitaires</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Gestion du personnel enseignant universitaire par UFR et département</p>
        </div>
        <div class="mt-4 sm:mt-0 flex space-x-3">
            <a href="{{ route('university.teachers.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouvel Enseignant
            </a>
            <button type="button" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                </svg>
                Exporter
            </button>
        </div>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Total Enseignants</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="p-3 bg-purple-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Actifs</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['active']) }}</p>
                </div>
                <div class="p-3 bg-green-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Professeurs</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['professors']) }}</p>
                </div>
                <div class="p-3 bg-blue-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm">Maîtres de Conf.</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['lecturers']) }}</p>
                </div>
                <div class="p-3 bg-indigo-400 bg-opacity-30 rounded-full">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recherche</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Nom, email, ID employé..."
                       class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">UFR</label>
                <select name="ufr_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Toutes les UFR</option>
                    @foreach($ufrs as $ufr)
                        <option value="{{ $ufr->id }}" {{ request('ufr_id') == $ufr->id ? 'selected' : '' }}>
                            {{ $ufr->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Département</label>
                <select name="department_id" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les départements</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Statut</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-700 dark:text-white">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactif</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspendu</option>
                </select>
            </div>

            <div class="flex items-end space-x-2">
                <button type="submit" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Filtrer
                </button>
                <a href="{{ route('university.teachers.index') }}" 
                   class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition-colors duration-200">
                    Réinitialiser
                </a>
            </div>
        </form>
    </div>

    <!-- Tableau des enseignants -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Enseignant
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            UFR / Département
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Grade
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Spécialisation
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($teachers as $teacher)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                                    {{ substr($teacher->first_name, 0, 1) }}{{ substr($teacher->last_name, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $teacher->full_name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $teacher->user->email }}
                                    </div>
                                    @if($teacher->employee_id)
                                    <div class="text-xs text-gray-400 dark:text-gray-500">
                                        ID: {{ $teacher->employee_id }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                @if($teacher->ufr)
                                    <div class="font-medium">{{ $teacher->ufr->name }}</div>
                                @endif
                                @if($teacher->department)
                                    <div class="text-gray-500 dark:text-gray-400">{{ $teacher->department->name }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($teacher->academic_rank)
                                @php
                                    $rankColors = [
                                        'professeur' => 'bg-purple-100 text-purple-800',
                                        'professeur_titulaire' => 'bg-purple-100 text-purple-800',
                                        'maitre_de_conferences' => 'bg-blue-100 text-blue-800',
                                        'maitre_assistant' => 'bg-green-100 text-green-800',
                                        'assistant' => 'bg-yellow-100 text-yellow-800',
                                        'charge_cours' => 'bg-gray-100 text-gray-800',
                                        'vacataire' => 'bg-orange-100 text-orange-800',
                                    ];
                                    $colorClass = $rankColors[$teacher->academic_rank] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $teacher->academic_rank)) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                {{ $teacher->specialization }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'inactive' => 'bg-gray-100 text-gray-800',
                                    'suspended' => 'bg-red-100 text-red-800',
                                ];
                                $statusClass = $statusColors[$teacher->status] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($teacher->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('university.teachers.show', $teacher) }}" 
                               class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('university.teachers.edit', $teacher) }}" 
                               class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button onclick="toggleStatus({{ $teacher->id }}, '{{ $teacher->status }}')"
                                    class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300">
                                @if($teacher->status === 'active')
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M19 10a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Aucun enseignant trouvé</p>
                                <p class="text-gray-500 dark:text-gray-400">Commencez par ajouter des enseignants à votre université.</p>
                                <a href="{{ route('university.teachers.create') }}" 
                                   class="mt-4 inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Ajouter un enseignant
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($teachers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $teachers->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function toggleStatus(teacherId, currentStatus) {
    if (!confirm('Êtes-vous sûr de vouloir modifier le statut de cet enseignant ?')) {
        return;
    }

    fetch(`{{ route('university.teachers.index') }}/${teacherId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Erreur lors de la modification du statut');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    });
}
</script>
@endsection