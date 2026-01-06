@extends('layouts.app')

@section('title', 'Dashboard Personnel')



@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête Dashboard -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Dashboard Personnel</h2>
                <p class="text-gray-600">
                    Gestion opérationnelle • 
                    <span class="font-semibold">{{ $user_role ?? 'Personnel' }}</span> •
                    {{ now()->format('d/m/Y') }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                    Système Opérationnel
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques d'aperçu opérationnel -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Étudiants Actifs</p>
                    <h3 class="text-2xl font-bold">{{ number_format($operational_stats['total_students'] ?? 0) }}</h3>
                </div>
                <i class="fas fa-user-graduate text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Enseignants Actifs</p>
                    <h3 class="text-2xl font-bold">{{ number_format($operational_stats['active_teachers'] ?? 0) }}</h3>
                </div>
                <i class="fas fa-chalkboard-teacher text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium mb-1">Inscriptions en Attente</p>
                    <h3 class="text-2xl font-bold">{{ number_format($operational_stats['pending_enrollments'] ?? 0) }}</h3>
                </div>
                <i class="fas fa-hourglass-half text-4xl text-yellow-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium mb-1">Santé Financière</p>
                    <h4 class="text-xl font-bold 
                        @switch($operational_stats['financial_health'] ?? 'good')
                            @case('excellent')
                                text-green-200
                                @break
                            @case('good')
                                text-blue-200
                                @break
                            @case('fair')
                                text-yellow-200
                                @break
                            @case('poor')
                                text-red-200
                                @break
                            @default
                                text-blue-200
                        @endswitch">
                        @switch($operational_stats['financial_health'] ?? 'good')
                            @case('excellent')
                                Excellente
                                @break
                            @case('good')
                                Bonne
                                @break
                            @case('fair')
                                Correcte
                                @break
                            @case('poor')
                                Faible
                                @break
                            @default
                                Bonne
                        @endswitch
                    </h4>
                </div>
                <i class="fas fa-chart-line text-4xl text-indigo-200"></i>
            </div>
        </div>
    </div>

    <!-- Alertes Prioritaires et Tâches -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Alertes Prioritaires -->
        @if(isset($priority_alerts) && $priority_alerts->isNotEmpty())
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-red-600 text-white px-6 py-4">
                <h5 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Alertes Prioritaires
                </h5>
            </div>
            <div class="p-6">
                @foreach($priority_alerts as $alert)
                    <div class="@if($alert['level'] === 'warning') bg-yellow-50 border-l-4 border-yellow-400 @else bg-blue-50 border-l-4 border-blue-400 @endif p-4 mb-3 rounded">
                        <div class="flex justify-between items-center">
                            <div>
                                <h6 class="font-semibold @if($alert['level'] === 'warning') text-yellow-800 @else text-blue-800 @endif mb-1">{{ $alert['title'] }}</h6>
                                <p class="@if($alert['level'] === 'warning') text-yellow-700 @else text-blue-700 @endif">{{ $alert['message'] }}</p>
                            </div>
                            @if(isset($alert['action_route']))
                                <a href="{{ route($alert['action_route']) }}" 
                                   class="px-3 py-1 @if($alert['level'] === 'warning') border border-yellow-500 text-yellow-700 hover:bg-yellow-100 @else border border-blue-500 text-blue-700 hover:bg-blue-100 @endif rounded text-sm font-medium transition-colors">
                                    Voir
                                </a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Tâches Quotidiennes -->
        <div class="@if(isset($priority_alerts) && $priority_alerts->isNotEmpty()) @else col-span-full @endif bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold flex items-center text-gray-900">
                    <i class="fas fa-tasks mr-2 text-gray-600"></i>
                    Tâches du Jour
                </h5>
            </div>
            <div class="p-6">
                @if(isset($daily_tasks) && count($daily_tasks) > 0)
                    @foreach($daily_tasks as $task)
                        <div class="border-l-4 @if($task['priority'] === 'high') border-red-500 @elseif($task['priority'] === 'medium') border-yellow-500 @else border-green-500 @endif bg-white rounded-r-lg shadow-sm p-4 mb-4">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <h6 class="font-semibold text-gray-900 mb-1">{{ $task['title'] }}</h6>
                                    <p class="text-gray-600 text-sm">{{ $task['description'] }}</p>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($task['count'] > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $task['count'] }}
                                        </span>
                                    @endif
                                    @if(isset($task['route']))
                                        <a href="{{ route($task['route']) }}" 
                                           class="inline-flex items-center px-3 py-1.5 border border-blue-500 text-blue-700 text-sm font-medium rounded hover:bg-blue-50 transition-colors">
                                            Traiter
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                        <p class="text-gray-500">Aucune tâche prioritaire aujourd'hui.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Résumé Financier et Activités -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Résumé Financier -->
        @if(isset($financial_summary))
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-600 text-white px-6 py-4">
                <h5 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-money-bill-wave mr-2"></i>
                    Résumé Financier
                </h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-4 text-center mb-6">
                    <div class="border-r border-gray-200 pr-4">
                        <h4 class="text-2xl font-bold text-green-600">{{ number_format($financial_summary['monthly_collected'] ?? 0) }} XOF</h4>
                        <p class="text-gray-600">Collecté ce mois</p>
                    </div>
                    <div class="pl-4">
                        <h4 class="text-2xl font-bold text-blue-600">{{ $financial_summary['collection_rate'] ?? 0 }}%</h4>
                        <p class="text-gray-600">Taux de recouvrement</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">Objectif mensuel</span>
                        <span class="font-semibold text-gray-900">{{ number_format($financial_summary['monthly_target'] ?? 0) }} XOF</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-700">En attente</span>
                        <span class="font-semibold text-yellow-600">{{ number_format($financial_summary['pending_amount'] ?? 0) }} XOF</span>
                    </div>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Activités Récentes -->
        <div class="@if(isset($financial_summary)) @else col-span-full @endif bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold flex items-center text-gray-900">
                    <i class="fas fa-history mr-2 text-gray-600"></i>
                    Activités Récentes
                </h5>
            </div>
            <div class="p-6">
                @if(isset($recent_activities) && $recent_activities->isNotEmpty())
                    <div class="space-y-4">
                        @foreach($recent_activities as $activity)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 mr-4">
                                    <i class="fas fa-{{ $activity['icon'] }} text-gray-400 text-lg"></i>
                                </div>
                                <div class="flex-1">
                                    <h6 class="font-semibold text-gray-900 mb-1">{{ $activity['title'] }}</h6>
                                    <p class="text-gray-600 text-sm mb-1">{{ $activity['description'] }}</p>
                                    <p class="text-gray-400 text-xs">{{ $activity['time']->diffForHumans() }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-clock text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Aucune activité récente.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <h5 class="text-lg font-semibold flex items-center text-gray-900">
                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                Actions Rapides
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @can('manage_payments')
                <a href="{{ route('payments.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors group">
                    <i class="fas fa-credit-card text-3xl text-blue-500 mb-3 group-hover:text-blue-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-blue-600">Gestion des Paiements</span>
                </a>
                @endcan
                
                @can('manage_enrollments')
                <a href="{{ route('enrollments.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-green-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors group">
                    <i class="fas fa-user-plus text-3xl text-green-500 mb-3 group-hover:text-green-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-green-600">Inscriptions</span>
                </a>
                @endcan
                
                @can('view_financial_reports')
                <a href="{{ route('reports.financial') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-indigo-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors group">
                    <i class="fas fa-chart-bar text-3xl text-indigo-500 mb-3 group-hover:text-indigo-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-indigo-600">Rapports Financiers</span>
                </a>
                @endcan
                
                @can('manage_communications')
                <a href="{{ route('communications.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-yellow-300 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors group">
                    <i class="fas fa-envelope text-3xl text-yellow-500 mb-3 group-hover:text-yellow-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-yellow-600">Communications</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Actualisation automatique des alertes
    setInterval(function() {
        // TODO: Implémenter actualisation AJAX des alertes prioritaires
        console.log('Dashboard staff - vérification des alertes...');
    }, 300000); // Chaque 5 minutes

    // Animation des cartes au survol
    $('.dashboard-card').hover(function() {
        $(this).addClass('shadow-lg');
    }, function() {
        $(this).removeClass('shadow-lg');
    });
</script>
@endpush