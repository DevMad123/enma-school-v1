@extends('layouts.app')

@section('title', 'Bulletins Scolaires')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Bulletins Scolaires</h1>
        <div class="flex space-x-3">
            <a href="{{ route('report-cards.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
                Générer Bulletin
            </a>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
        <form method="GET" action="{{ route('report-cards.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="student_id" class="block text-sm font-medium text-gray-700 mb-1">Étudiant</label>
                <select name="student_id" id="student_id" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Tous les étudiants</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->full_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="period_id" class="block text-sm font-medium text-gray-700 mb-1">Période</label>
                <select name="period_id" id="period_id" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Toutes les périodes</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" {{ request('period_id') == $period->id ? 'selected' : '' }}>
                            {{ $period->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                <select name="status" id="status" class="w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Tous les statuts</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Brouillon</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Publié</option>
                    <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>Archivé</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Génération en masse -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <h3 class="font-medium text-yellow-800 mb-2">Génération en masse</h3>
        <form method="POST" action="{{ route('report-cards.bulk-generate') }}" class="flex items-end space-x-3">
            @csrf
            <div>
                <label for="class_id" class="block text-sm font-medium text-yellow-700 mb-1">Classe</label>
                <select name="class_id" id="class_id" required class="rounded-md border-yellow-300 shadow-sm">
                    <option value="">Sélectionner une classe</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }} ({{ $class->level->name }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="period_id_bulk" class="block text-sm font-medium text-yellow-700 mb-1">Période</label>
                <select name="period_id" id="period_id_bulk" required class="rounded-md border-yellow-300 shadow-sm">
                    <option value="">Sélectionner une période</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md">
                Générer pour la classe
            </button>
        </form>
    </div>

    <!-- Liste des bulletins -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Étudiant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Période</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moyenne</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($reportCards as $reportCard)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $reportCard->student->full_name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $reportCard->schoolClass->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $reportCard->gradePeriod->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($reportCard->general_average !== null)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $reportCard->general_average >= 10 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ number_format($reportCard->general_average, 2) }}/20
                                    </span>
                                @else
                                    <span class="text-gray-400">Non calculé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($reportCard->class_rank && $reportCard->total_students_in_class)
                                    {{ $reportCard->class_rank }}/{{ $reportCard->total_students_in_class }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @switch($reportCard->status)
                                        @case('draft') bg-gray-100 text-gray-800 @break
                                        @case('published') bg-blue-100 text-blue-800 @break
                                        @case('archived') bg-yellow-100 text-yellow-800 @break
                                    @endswitch">
                                    {{ ucfirst($reportCard->status) }}
                                    @if($reportCard->is_final)
                                        <svg class="ml-1 w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"/>
                                        </svg>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <a href="{{ route('report-cards.show', $reportCard) }}" 
                                       class="text-blue-600 hover:text-blue-900">Voir</a>
                                    
                                    @if($reportCard->general_average !== null)
                                        <a href="{{ route('report-cards.pdf', $reportCard) }}" 
                                           class="text-green-600 hover:text-green-900" title="Télécharger PDF">PDF</a>
                                    @endif
                                    
                                    @if(!$reportCard->is_final)
                                        <a href="{{ route('report-cards.edit', $reportCard) }}" 
                                           class="text-yellow-600 hover:text-yellow-900">Éditer</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Aucun bulletin trouvé. 
                                <a href="{{ route('report-cards.create') }}" class="text-blue-600 hover:text-blue-800">
                                    Générer le premier bulletin
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($reportCards->hasPages())
        <div class="mt-6">
            {{ $reportCards->withQueryString()->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
// Confirmation avant génération en masse
document.querySelector('form[action*="bulk-generate"]').addEventListener('submit', function(e) {
    if (!confirm('Êtes-vous sûr de vouloir générer les bulletins pour toute la classe ?')) {
        e.preventDefault();
    }
});
</script>
@endpush
@endsection