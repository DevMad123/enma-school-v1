@extends('layouts.dashboard')

@section('title', 'Journaux de Connexion')

@section('content')
<div class="mx-auto">
    <!-- En-tête -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">🔒 Journaux de Connexion & Audits</h1>
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
                    <li class="text-sm font-medium text-gray-900">Journaux de Connexion</li>
                </ol>
            </nav>
        </div>
        <button class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" onclick="exportLogs()">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter Logs
        </button>
    </div>

    <!-- Statistiques des connexions -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-blue-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">🔑</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Connexions</dt>
                            <dd class="text-3xl font-bold text-blue-600">{{ $loginStats['total_logins'] }}</dd>
                            <dd class="text-xs text-gray-400">Période sélectionnée</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-green-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">👥</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Utilisateurs Uniques</dt>
                            <dd class="text-3xl font-bold text-green-600">{{ $loginStats['unique_users'] }}</dd>
                            <dd class="text-xs text-gray-400">Ont utilisé le système</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-indigo-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">🚪</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Déconnexions</dt>
                            <dd class="text-3xl font-bold text-indigo-600">{{ $loginStats['total_logouts'] }}</dd>
                            <dd class="text-xs text-gray-400">Sessions fermées</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg border-l-4 border-yellow-500">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="text-2xl">⚖️</div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Ratio Ouverture</dt>
                            <dd class="text-3xl font-bold text-yellow-600">
                                {{ $loginStats['total_logins'] > 0 ? round(($loginStats['total_logouts'] / $loginStats['total_logins']) * 100) : 0 }}%
                            </dd>
                            <dd class="text-xs text-gray-400">Sessions fermées proprement</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Répartition par rôle et graphique horaire -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">👤 Répartition par Rôle</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                @if($loginStats['by_role']->count() > 0)
                    <div class="h-48">
                        <canvas id="roleChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Aucune donnée disponible</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">🕐 Distribution Horaire</h3>
            </div>
            <div class="px-4 py-5 sm:p-6">
                @if($loginStats['hourly_distribution']->count() > 0)
                    <div class="h-48">
                        <canvas id="hourlyChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="mt-2 text-sm text-gray-500">Aucune donnée disponible</p>
                    </div>
                @endif
            </div>
        </div>
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
                <div>
                    <label for="action" class="block text-sm font-medium text-gray-700 mb-2">Type d'action</label>
                    <select name="action" id="action" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 rounded-md">
                        <option value="">Toutes les actions</option>
                        <option value="logged_in" {{ $action === 'logged_in' ? 'selected' : '' }}>Connexions</option>
                        <option value="logged_out" {{ $action === 'logged_out' ? 'selected' : '' }}>Déconnexions</option>
                    </select>
                </div>
                <div class="flex items-end space-x-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        Filtrer
                    </button>
                    <a href="{{ route('admin.supervision.user-logs') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Réinitialiser
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Table des logs -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">📋 Historique Détaillé</h3>
        </div>
        <div class="px-4 py-5 sm:p-6">
            @if($logs->count() > 0)
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date & Heure</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rôle</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adresse IP</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Navigateur</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="font-medium text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                                    <div class="text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8">
                                            <div class="h-8 w-8 bg-gray-600 rounded-full flex items-center justify-center text-white font-medium text-xs">
                                                {{ substr($log->user->name, 0, 2) }}
                                            </div>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $log->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $log->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->user->hasRole('admin'))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">👑 Admin</span>
                                    @elseif($log->user->isTeacher())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">🧑‍🏫 Enseignant</span>
                                    @elseif($log->user->isStudent())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">🎓 Étudiant</span>
                                    @elseif($log->user->isParent())
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">👨‍👩‍👧‍👦 Parent</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">👤 Utilisateur</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($log->action === 'logged_in')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">🔓 Connexion</span>
                                    @elseif($log->action === 'logged_out')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">🔒 Déconnexion</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ ucfirst($log->action) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <code class="text-xs text-pink-600 bg-gray-100 px-2 py-1 rounded">{{ $log->ip_address }}</code>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @php
                                        $userAgent = $log->user_agent;
                                        if (str_contains($userAgent, 'Chrome')) {
                                            $browser = '🌐 Chrome';
                                        } elseif (str_contains($userAgent, 'Firefox')) {
                                            $browser = '🦊 Firefox';
                                        } elseif (str_contains($userAgent, 'Safari')) {
                                            $browser = '🧭 Safari';
                                        } elseif (str_contains($userAgent, 'Edge')) {
                                            $browser = '🔷 Edge';
                                        } else {
                                            $browser = '❓ Autre';
                                        }
                                    @endphp
                                    {{ $browser }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $log->description ?? 'N/A' }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="flex justify-center mt-6">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun log trouvé</h3>
                    <p class="mt-1 text-sm text-gray-500">Aucun log trouvé pour cette période</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Graphique par rôle
    @if($loginStats['by_role']->count() > 0)
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: {!! $loginStats['by_role']->pluck('role')->toJson() !!},
            datasets: [{
                data: {!! $loginStats['by_role']->pluck('count')->toJson() !!},
                backgroundColor: [
                    '#ef4444', // Admin (red)
                    '#3b82f6', // Enseignant (blue)
                    '#10b981', // Étudiant (green)
                    '#6366f1', // Parent (indigo)
                    '#6b7280'  // Autre (gray)
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
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
    @endif

    // Graphique horaire
    @if($loginStats['hourly_distribution']->count() > 0)
    const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(hourlyCtx, {
        type: 'bar',
        data: {
            labels: {!! $loginStats['hourly_distribution']->pluck('hour')->map(function($h) { return $h . 'h'; })->toJson() !!},
            datasets: [{
                label: 'Connexions',
                data: {!! $loginStats['hourly_distribution']->pluck('count')->toJson() !!},
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: '#3b82f6',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
    @endif
});

function exportLogs() {
    const dateFrom = document.querySelector('input[name="date_from"]').value;
    const dateTo = document.querySelector('input[name="date_to"]').value;
    const action = document.querySelector('select[name="action"]').value;
    
    const params = new URLSearchParams({
        export: 'csv',
        date_from: dateFrom,
        date_to: dateTo,
        action: action
    });
    
    window.open(`{{ route('admin.supervision.user-logs') }}?${params}`, '_blank');
}
</script>
@endsection