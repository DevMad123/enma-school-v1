@extends('layouts.dashboard')

@section('title', 'Activités des Enseignants')

@section('content')
<div class="mx-auto">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🧑‍🏫 Activités des Enseignants</h1>
            <nav class="flex mt-2" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-4">
                    <li>
                        <a href="{{ route('admin.supervision.index') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium">
                            Supervision
                        </a>
                    </li>
                    <li>
                        <svg class="flex-shrink-0 h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </li>
                    <li class="text-sm font-medium text-gray-900">Activités Enseignants</li>
                </ol>
            </nav>
        </div>
        <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="exportData()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter
        </button>
    </div>

    <!-- Filtres -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date de début</label>
                    <input type="date" name="date_from" id="date_from" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $dateFrom }}">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date de fin</label>
                    <input type="date" name="date_to" id="date_to" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ $dateTo }}">
                </div>
                <div class="flex items-end space-x-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('admin.supervision.teacher-activities') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistiques des enseignants -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">📊 Performance des Enseignants</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enseignant</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Activités</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cours Créés</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Devoirs Donnés</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes Attribuées</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dernière Activité</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score Performance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($teacherStats as $stat)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 bg-indigo-600 rounded-full flex items-center justify-center text-white font-medium text-sm">
                                            {{ substr($stat['teacher']->user->name, 0, 2) }}
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $stat['teacher']->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $stat['teacher']->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $stat['total_activities'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['courses_created'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['assignments_created'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $stat['grades_given'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($stat['last_activity'])
                                    {{ $stat['last_activity']->diffForHumans() }}
                                @else
                                    <span class="text-gray-400">Aucune</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $score = min(100, ($stat['total_activities'] * 10));
                                    $badgeClass = $score >= 70 ? 'bg-green-100 text-green-800' : ($score >= 40 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ $score }}%
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        
                        @if($teacherStats->count() === 0)
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Aucune donnée pour cette période</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Journal détaillé des activités -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">📝 Journal Détaillé des Activités</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
            @if($activities->count() > 0)
                <div class="flow-root">
                    <ul role="list" class="-mb-8">
                        @foreach($activities as $activity)
                        <li>
                            <div class="relative pb-8">
                                @if(!$loop->last)
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                @endif
                                <div class="relative flex space-x-3">
                                    <div>
                                        @php
                                            $colorClass = match($activity->action) {
                                                'created_evaluation', 'created_assignment' => 'bg-green-500',
                                                'updated_grade', 'updated_evaluation' => 'bg-yellow-500',
                                                'deleted_evaluation' => 'bg-red-500',
                                                default => 'bg-blue-500'
                                            };
                                        @endphp
                                        <span class="h-8 w-8 rounded-full {{ $colorClass }} flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="bg-gray-50 px-4 py-3 rounded-lg border-l-4 border-indigo-400">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <p class="text-sm font-medium text-gray-900 mb-2">
                                                        <span class="font-semibold">{{ $activity->user->name }}</span>
                                                        <span class="text-indigo-600">{{ getActivityDescription($activity) }}</span>
                                                        @if($activity->entity_id)
                                                            #{{ $activity->entity_id }}
                                                        @endif
                                                    </p>
                                                    @if($activity->properties && is_array($activity->properties))
                                                        <div class="text-xs text-gray-600 mb-2">
                                                            @foreach($activity->properties as $key => $value)
                                                                <span class="inline-block mr-3">
                                                                    <strong>{{ ucfirst($key) }}:</strong> {{ $value }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                    <p class="text-xs text-gray-500 flex items-center">
                                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $activity->created_at->format('d/m/Y H:i:s') }}
                                                    </p>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-3">
                                                    {{ ucfirst($activity->entity) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    {{ $activities->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucune activité trouvée</h3>
                    <p class="mt-1 text-sm text-gray-500">Aucune activité trouvée pour cette période</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportData() {
    const dateFrom = document.querySelector('input[name="date_from"]').value;
    const dateTo = document.querySelector('input[name="date_to"]').value;
    
    const params = new URLSearchParams({
        export: 'csv',
        date_from: dateFrom,
        date_to: dateTo
    });
    
    window.open(`{{ route('admin.supervision.teacher-activities') }}?${params}`, '_blank');
}
</script>
@endsection