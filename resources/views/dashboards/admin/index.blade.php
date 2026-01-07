@extends('layouts.dashboard')

@section('title', 'Dashboard Administration')



@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête Dashboard -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Dashboard Administration</h2>
                <p class="text-gray-600">
                    Vue d'ensemble et pilotage de l'établissement • 
                    <span class="font-semibold">{{ $overview_stats['school_context'] }}</span>
                </p>
            </div>
            <div class="mt-3 sm:mt-0 text-right">
                <div class="text-gray-500 text-sm">Dernière mise à jour</div>
                <div class="font-bold text-gray-900">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

    <!-- Statistiques d'aperçu -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Étudiants Inscrits</p>
                    <h3 class="text-2xl font-bold">{{ number_format($overview_stats['total_students']) }}</h3>
                </div>
                <i class="fas fa-user-graduate text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Enseignants</p>
                    <h3 class="text-2xl font-bold">{{ number_format($overview_stats['total_teachers']) }}</h3>
                </div>
                <i class="fas fa-chalkboard-teacher text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium mb-1">Classes</p>
                    <h3 class="text-2xl font-bold">{{ number_format($overview_stats['total_classes']) }}</h3>
                </div>
                <i class="fas fa-home text-4xl text-indigo-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm font-medium mb-1">Utilisateurs Système</p>
                    <h3 class="text-2xl font-bold">{{ number_format($overview_stats['total_users']) }}</h3>
                </div>
                <i class="fas fa-users text-4xl text-yellow-200"></i>
            </div>
        </div>
    </div>

    <!-- Statistiques Financières et Supervision -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Finances -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4">
                    <h5 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-chart-line mr-2"></i>
                        Vue Financière
                    </h5>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-center">
                        <div class="border-r border-gray-200">
                            <h4 class="text-2xl font-bold text-green-600">{{ number_format($financial_stats['total_revenue']) }} XOF</h4>
                            <p class="text-gray-600">Revenus Totaux</p>
                        </div>
                        <div class="border-r border-gray-200">
                            <h4 class="text-2xl font-bold text-blue-600">{{ number_format($financial_stats['monthly_revenue']) }} XOF</h4>
                            <p class="text-gray-600">Revenus du Mois</p>
                        </div>
                        <div class="border-r border-gray-200">
                            <h4 class="text-2xl font-bold text-yellow-600">{{ $financial_stats['pending_payments'] }}</h4>
                            <p class="text-gray-600">Paiements en Attente</p>
                        </div>
                        <div>
                            <h4 class="text-2xl font-bold text-indigo-600">{{ $financial_stats['collection_rate'] }}%</h4>
                            <p class="text-gray-600">Taux de Recouvrement</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Supervision -->
        <div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 text-white px-6 py-4">
                    <h5 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-eye mr-2"></i>
                        Supervision Système
                    </h5>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-gray-700">Connexions Aujourd'hui</span>
                            <strong class="text-gray-900">{{ $supervision_data['today_logins'] }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Utilisateurs Actifs</span>
                            <strong class="text-gray-900">{{ $supervision_data['active_users_week'] }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">Activités Système</span>
                            <strong class="text-gray-900">{{ $supervision_data['system_activities'] }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-700">État Système</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Opérationnel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides et Activités Récentes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Actions Rapides -->
        <div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @foreach($quick_actions as $action)
                            @can($action['permission'])
                                <a href="{{ route($action['route']) }}" 
                                   class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-{{ $action['color'] }}-300 rounded-lg hover:border-{{ $action['color'] }}-500 hover:bg-{{ $action['color'] }}-50 transition-colors group">
                                    <i class="fas fa-{{ $action['icon'] }} mr-2 text-{{ $action['color'] }}-500"></i>
                                    <span class="text-{{ $action['color'] }}-700 group-hover:text-{{ $action['color'] }}-800">{{ $action['title'] }}</span>
                                </a>
                            @endcan
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Activités Récentes -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-history mr-2 text-gray-600"></i>
                        Activités Récentes
                    </h5>
                </div>
                <div class="p-6">
                    @if($recent_activities->isNotEmpty())
                        <div class="space-y-4">
                            @foreach($recent_activities as $activity)
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-{{ $activity['icon'] }} text-gray-500"></i>
                                    </div>
                                    <div class="ml-3">
                                        <h6 class="text-sm font-medium text-gray-900 mb-1">{{ $activity['title'] }}</h6>
                                        <p class="text-gray-600 text-sm mb-1">{{ $activity['description'] }}</p>
                                        <small class="text-gray-500 text-xs">{{ $activity['date']->diffForHumans() }}</small>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-8">Aucune activité récente.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tendance des inscriptions -->
    @if($enrollment_trend->isNotEmpty())
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-area mr-2 text-gray-600"></i>
                    Évolution des Inscriptions (6 derniers mois)
                </h5>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-6 gap-4 text-center">
                    @foreach($enrollment_trend as $month)
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900 mb-1">{{ $month['count'] }}</h4>
                            <p class="text-gray-600 text-sm">{{ $month['month'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Actualisation automatique des données de supervision
    setInterval(function() {
        // TODO: Implémenter actualisation AJAX des données critiques
        console.log('Dashboard admin - actualisation des données...');
    }, 60000); // Chaque minute

    // Initialisation des tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush