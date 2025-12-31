@extends('layouts.dashboard')

@section('title', 'Affectations des Enseignants')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Affectations des Enseignants</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Gérer les affectations des enseignants aux classes</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('teacher-assignments.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nouvelle Affectation
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
                            
                            @if (session('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                                    {{ session('success') }}
                                </div>
                            @endif

                            <!-- Statistiques rapides -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                                <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-blue-800 dark:text-blue-200">Total Affectations</h3>
                                    <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                        {{ $assignments->total() }}
                                    </p>
                                </div>
                                <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-green-800 dark:text-green-200">Enseignants Actifs</h3>
                                    <p class="text-2xl font-bold text-green-600 dark:text-green-400">
                                        {{ \App\Models\Teacher::where('status', 'active')->count() }}
                                    </p>
                                </div>
                                <div class="bg-purple-100 dark:bg-purple-900 p-4 rounded-lg">
                                    <h3 class="text-lg font-semibold text-purple-800 dark:text-purple-200">Classes Couvertes</h3>
                                    <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                        {{ $assignments->unique('class_id')->count() }}
                                    </p>
                                </div>
                            </div>

                            <!-- Table des affectations -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Enseignant
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Spécialisation
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Classe
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Année Académique
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Date d'affectation
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        @forelse ($assignments as $assignment)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $assignment->teacher->full_name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ $assignment->teacher->user->email }}
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $assignment->teacher->specialization ?: 'Non spécifiée' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        {{ $assignment->schoolClass->level->name }} {{ $assignment->schoolClass->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        Capacité: {{ $assignment->schoolClass->capacity }} élèves
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $assignment->academicYear->name }}
                                                    @if($assignment->academicYear->is_active)
                                                        <span class="ml-2 px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Actuelle</span>
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                                    {{ $assignment->created_at->format('d/m/Y à H:i') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <a href="{{ route('teacher-assignments.show', $assignment) }}" 
                                                       class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-600">
                                                        Voir
                                                    </a>
                                                    
                                                    <a href="{{ route('teacher-assignments.edit', $assignment) }}" 
                                                       class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-600">
                                                        Modifier
                                                    </a>

                                                    <form method="POST" action="{{ route('teacher-assignments.destroy', $assignment) }}" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-600"
                                                                onclick="return confirm('Supprimer cette affectation ?')">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                                    Aucune affectation trouvée.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                {{ $assignments->links() }}
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection