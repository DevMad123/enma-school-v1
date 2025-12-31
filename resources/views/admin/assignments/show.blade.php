@extends('layouts.dashboard')

@section('title', 'Détail de l\'affectation')

@section('header')
<div class="flex items-center justify-between">
    <div class="flex items-center space-x-3">
        <a href="{{ route('admin.assignments.index') }}" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
        </svg>
        <h1 class="text-2xl font-bold text-gray-800">Affectation pédagogique</h1>
    </div>
    <div class="flex items-center space-x-3">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
            {{ $assignment->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
            {{ $assignment->is_active ? 'Active' : 'Inactive' }}
        </span>
        
        @if($assignment->is_active)
            <form action="{{ route('admin.assignments.toggle-status', $assignment) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline-warning btn-sm" 
                        onclick="return confirm('Êtes-vous sûr de vouloir désactiver cette affectation ?')">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Désactiver
                </button>
            </form>
        @else
            <form action="{{ route('admin.assignments.toggle-status', $assignment) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M19 10a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Réactiver
                </button>
            </form>
        @endif
        
        <a href="{{ route('admin.assignments.edit', $assignment) }}" class="btn btn-outline-primary btn-sm">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier
        </a>
        
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="btn btn-secondary btn-sm">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                </svg>
                Actions
            </button>
            <div x-show="open" @click.away="open = false" 
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 class="absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10">
                <div class="py-1">
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Dupliquer l'affectation
                    </a>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        Exporter les détails
                    </a>
                    <hr class="my-1">
                    <button onclick="confirmDelete()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                        Supprimer l'affectation
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Informations principales -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informations principales
            </h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Enseignant -->
                <div class="bg-blue-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">
                            {{ strtoupper(substr($assignment->teacher->user->name, 0, 2)) }}
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Enseignant</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $assignment->teacher->user->name }}</p>
                            @if($assignment->teacher->specialization)
                                <p class="text-sm text-gray-600">{{ $assignment->teacher->specialization }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Matière -->
                <div class="bg-purple-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Matière</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $assignment->subject->name }}</p>
                            @if($assignment->subject->code)
                                <p class="text-sm text-gray-600">Code: {{ $assignment->subject->code }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Classe -->
                <div class="bg-green-50 rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Classe</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $assignment->schoolClass->name }}</p>
                            <p class="text-sm text-gray-600">{{ $assignment->schoolClass->level->name }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Détails de l'affectation -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-2xl font-bold text-indigo-600">{{ $assignment->weekly_hours }}h</p>
                    <p class="text-sm text-gray-600">Heures par semaine</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-lg font-bold text-gray-900">{{ ucfirst($assignment->assignment_type) }}</p>
                    <p class="text-sm text-gray-600">Type d'affectation</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-lg font-bold text-gray-900">{{ $assignment->start_date->format('d/m/Y') }}</p>
                    <p class="text-sm text-gray-600">Date de début</p>
                </div>
                <div class="text-center p-4 bg-gray-50 rounded-lg">
                    <p class="text-lg font-bold text-gray-900">
                        {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : 'Permanent' }}
                    </p>
                    <p class="text-sm text-gray-600">Date de fin</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Informations contextuelles -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Détails de l'enseignant -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    Profil de l'enseignant
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Employé ID:</span>
                        <span class="text-sm text-gray-900">{{ $assignment->teacher->employee_id }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Email:</span>
                        <span class="text-sm text-gray-900">{{ $assignment->teacher->user->email }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Téléphone:</span>
                        <span class="text-sm text-gray-900">
                            {{ $assignment->teacher->user->phone ?? 'Non renseigné' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Date d'embauche:</span>
                        <span class="text-sm text-gray-900">
                            {{ $assignment->teacher->hire_date?->format('d/m/Y') ?? 'Non renseigné' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Type d'emploi:</span>
                        <span class="text-sm text-gray-900">
                            {{ $assignment->teacher->employment_type ? 
                               str_replace(['full_time', 'part_time', 'contract', 'substitute'], 
                                          ['Temps plein', 'Temps partiel', 'Contractuel', 'Remplaçant'], 
                                          $assignment->teacher->employment_type) : 'Non renseigné' }}
                        </span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t">
                    <a href="{{ route('admin.teachers.show', $assignment->teacher) }}" 
                       class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                        Voir le profil complet →
                    </a>
                </div>
            </div>
        </div>

        <!-- Informations sur la classe et année -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Contexte pédagogique
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Année académique:</span>
                        <span class="text-sm text-gray-900">{{ $assignment->academicYear->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Période:</span>
                        <span class="text-sm text-gray-900">
                            {{ $assignment->academicYear->start_date->format('Y') }} - 
                            {{ $assignment->academicYear->end_date->format('Y') }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Niveau:</span>
                        <span class="text-sm text-gray-900">{{ $assignment->schoolClass->level->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm font-medium text-gray-500">Cycle:</span>
                        <span class="text-sm text-gray-900">{{ $assignment->schoolClass->level->cycle->name ?? 'Non défini' }}</span>
                    </div>
                    @if($assignment->schoolClass->students_count)
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-500">Nombre d'élèves:</span>
                            <span class="text-sm text-gray-900">{{ $assignment->schoolClass->students_count }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Notes et historique -->
    @if($assignment->notes || $assignment->created_at)
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Notes et informations complémentaires
                </h3>
            </div>
            <div class="p-6">
                @if($assignment->notes)
                    <div class="mb-4">
                        <p class="text-sm font-medium text-gray-700 mb-2">Notes:</p>
                        <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ $assignment->notes }}</p>
                    </div>
                @endif
                
                <div class="text-xs text-gray-500 space-y-1">
                    <p>Affectation créée le {{ $assignment->created_at->format('d/m/Y à H:i') }}</p>
                    @if($assignment->updated_at && $assignment->updated_at != $assignment->created_at)
                        <p>Dernière modification le {{ $assignment->updated_at->format('d/m/Y à H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Autres affectations du même enseignant -->
    @if($relatedAssignments->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    Autres affectations de {{ $assignment->teacher->user->name }}
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Matière
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Classe
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Heures/sem
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($relatedAssignments as $related)
                            <tr class="hover:bg-gray-50 {{ $related->id === $assignment->id ? 'bg-blue-50' : '' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $related->subject->name }}
                                    @if($related->id === $assignment->id)
                                        <span class="ml-2 text-xs text-blue-600">(actuel)</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $related->schoolClass->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $related->weekly_hours }}h
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $related->assignment_type === 'primary' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($related->assignment_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $related->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $related->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

<!-- Modal de suppression -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mt-4">Confirmer la suppression</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    Êtes-vous sûr de vouloir supprimer cette affectation ? Cette action est irréversible.
                </p>
            </div>
            <div class="items-center px-4 py-3">
                <form action="{{ route('admin.assignments.destroy', $assignment) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md shadow-sm hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 mr-2">
                        Supprimer
                    </button>
                </form>
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 text-gray-800 text-base font-medium rounded-md shadow-sm hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-300">
                    Annuler
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
function confirmDelete() {
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>
@endpush
@endsection