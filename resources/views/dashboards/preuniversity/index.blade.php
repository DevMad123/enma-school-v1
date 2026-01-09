@extends('layouts.admin')

@section('title', 'Dashboard Préuniversitaire')

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête -->
    <div class="mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $school->name }}</h1>
                <p class="text-gray-600">Dashboard Préuniversitaire - {{ $academicYear->name }}</p>
            </div>
            <div class="flex space-x-3">
                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <i class="fas fa-download mr-2"></i>Exporter
                </button>
                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    <i class="fas fa-cog mr-2"></i>Paramètres
                </button>
            </div>
        </div>
    </div>

    <!-- Alertes -->
    @if(count($alerts) > 0)
        <div class="mb-6 space-y-3">
            @foreach($alerts as $alert)
                <div class="p-4 rounded-md {{ $alert['type'] === 'danger' ? 'bg-red-100 border border-red-400 text-red-700' : ($alert['type'] === 'warning' ? 'bg-yellow-100 border border-yellow-400 text-yellow-700' : 'bg-blue-100 border border-blue-400 text-blue-700') }}">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <i class="fas {{ $alert['type'] === 'danger' ? 'fa-exclamation-triangle' : ($alert['type'] === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle') }} mr-2"></i>
                            <div>
                                <h4 class="font-medium">{{ $alert['title'] }}</h4>
                                <p class="text-sm">{{ $alert['message'] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white bg-opacity-50">
                                {{ $alert['count'] }}
                            </span>
                            <a href="{{ route($alert['route']) }}" class="text-sm font-medium hover:underline">
                                Voir <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Statistiques principales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-6 mb-8">
        <!-- Total Élèves -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user-graduate text-blue-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Élèves inscrits</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($stats['total_students']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Classes -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chalkboard text-green-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Classes actives</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_classes'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Enseignants -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chalkboard-teacher text-purple-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Enseignants</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_teachers'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Matières -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-book text-yellow-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Matières</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total_subjects'] }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Taux de réussite -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-chart-line text-indigo-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Taux réussite</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['success_rate'] }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Moyenne générale -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-trophy text-red-600"></i>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Moyenne générale</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['global_average'] }}/20</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Graphiques et tableaux -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Distribution par niveau -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Distribution par niveau</h3>
            <div class="h-64">
                <canvas id="levelDistributionChart"></canvas>
            </div>
        </div>

        <!-- Évolution des inscriptions -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Évolution des inscriptions</h3>
            <div class="h-64">
                <canvas id="enrollmentTrendChart"></canvas>
            </div>
        </div>

        <!-- Répartition par sexe -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition par sexe</h3>
            <div class="h-64">
                <canvas id="genderDistributionChart"></canvas>
            </div>
        </div>

        <!-- Moyennes par classe -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Moyennes par classe</h3>
            <div class="h-64">
                <canvas id="classAveragesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Métriques par niveau -->
    <div class="bg-white shadow rounded-lg mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Métriques par niveau</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cycle/Niveau</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Élèves</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacité moyenne</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Taux occupation</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($levelMetrics as $metric)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $metric->level_name }}</div>
                                    <div class="text-xs text-gray-500">{{ $metric->cycle_name }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $metric->total_classes }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $metric->total_students }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($metric->average_capacity, 0) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full {{ $metric->occupancy_rate > 100 ? 'bg-red-500' : ($metric->occupancy_rate > 80 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ min($metric->occupancy_rate, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs {{ $metric->occupancy_rate > 100 ? 'text-red-600' : 'text-gray-600' }}">
                                        {{ number_format($metric->occupancy_rate, 1) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('academic.classes.index', ['level_id' => $metric->id]) }}" class="text-blue-600 hover:text-blue-900">
                                    Voir classes
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activités récentes -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Inscriptions récentes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Inscriptions récentes</h3>
            </div>
            <div class="flow-root">
                <ul class="divide-y divide-gray-200">
                    @forelse($recentActivities['enrollments'] as $activity)
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-plus text-blue-600 text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $activity['title'] }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $activity['description'] }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $activity['date']->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route($activity['route'], $activity['route_params']) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                        Voir
                                    </a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-center text-gray-500">
                            Aucune inscription récente
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        <!-- Évaluations récentes -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Évaluations récentes</h3>
            </div>
            <div class="flow-root">
                <ul class="divide-y divide-gray-200">
                    @forelse($recentActivities['evaluations'] as $activity)
                        <li class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-clipboard-check text-green-600 text-xs"></i>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $activity['title'] }}
                                    </p>
                                    <p class="text-sm text-gray-500 truncate">
                                        {{ $activity['description'] }}
                                    </p>
                                    <p class="text-xs text-gray-400">
                                        {{ $activity['date']->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex-shrink-0">
                                    <a href="{{ route($activity['route'], $activity['route_params']) }}" class="text-sm text-blue-600 hover:text-blue-900">
                                        Voir
                                    </a>
                                </div>
                            </div>
                        </li>
                    @empty
                        <li class="px-6 py-4 text-center text-gray-500">
                            Aucune évaluation récente
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuration des couleurs
    const colors = {
        primary: '#3B82F6',
        secondary: '#10B981',
        tertiary: '#F59E0B',
        danger: '#EF4444',
        purple: '#8B5CF6',
        indigo: '#6366F1'
    };

    // Distribution par niveau
    const levelDistributionCtx = document.getElementById('levelDistributionChart').getContext('2d');
    const levelData = @json($chartsData['level_distribution']);
    
    new Chart(levelDistributionCtx, {
        type: 'doughnut',
        data: {
            labels: levelData.map(item => item.name),
            datasets: [{
                data: levelData.map(item => item.student_count),
                backgroundColor: [colors.primary, colors.secondary, colors.tertiary, colors.danger, colors.purple, colors.indigo]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Évolution des inscriptions
    const enrollmentTrendCtx = document.getElementById('enrollmentTrendChart').getContext('2d');
    const enrollmentData = @json($chartsData['enrollment_trend']);
    
    new Chart(enrollmentTrendCtx, {
        type: 'line',
        data: {
            labels: enrollmentData.map(item => item.date),
            datasets: [{
                label: 'Inscriptions',
                data: enrollmentData.map(item => item.count),
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Répartition par sexe
    const genderDistributionCtx = document.getElementById('genderDistributionChart').getContext('2d');
    const genderData = @json($chartsData['gender_distribution']);
    
    new Chart(genderDistributionCtx, {
        type: 'pie',
        data: {
            labels: genderData.map(item => item.gender === 'M' ? 'Garçons' : 'Filles'),
            datasets: [{
                data: genderData.map(item => item.count),
                backgroundColor: [colors.primary, colors.secondary]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Moyennes par classe
    const classAveragesCtx = document.getElementById('classAveragesChart').getContext('2d');
    const classData = @json($chartsData['class_averages']);
    
    new Chart(classAveragesCtx, {
        type: 'bar',
        data: {
            labels: classData.map(item => item.class_name),
            datasets: [{
                label: 'Moyenne',
                data: classData.map(item => item.average),
                backgroundColor: colors.secondary,
                borderColor: colors.secondary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 20,
                    ticks: {
                        stepSize: 2
                    }
                }
            }
        }
    });
});
</script>
@endpush