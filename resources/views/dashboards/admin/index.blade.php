@extends('layouts.app')

@section('title', 'Dashboard Administration')



@section('content')
<div class="container-fluid">
    <!-- En-tête Dashboard -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">Dashboard Administration</h2>
                    <p class="text-muted mb-0">
                        Vue d'ensemble et pilotage de l'établissement • 
                        <strong>{{ $overview_stats['school_context'] }}</strong>
                    </p>
                </div>
                <div class="text-end">
                    <div class="text-muted small">Dernière mise à jour</div>
                    <div class="fw-bold">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques d'aperçu -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-graduate fa-2x text-primary"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-title text-muted mb-1">Étudiants Inscrits</h6>
                            <h3 class="mb-0">{{ number_format($overview_stats['total_students']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-chalkboard-teacher fa-2x text-success"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-title text-muted mb-1">Enseignants</h6>
                            <h3 class="mb-0">{{ number_format($overview_stats['total_teachers']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-home fa-2x text-info"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-title text-muted mb-1">Classes</h6>
                            <h3 class="mb-0">{{ number_format($overview_stats['total_classes']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card dashboard-card stat-card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x text-warning"></i>
                        </div>
                        <div class="ms-3">
                            <h6 class="card-title text-muted mb-1">Utilisateurs Système</h6>
                            <h3 class="mb-0">{{ number_format($overview_stats['total_users']) }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistiques Financières et Supervision -->
    <div class="row mb-4">
        <!-- Finances -->
        <div class="col-lg-8 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Vue Financière
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center border-end">
                            <h4 class="text-success">{{ number_format($financial_stats['total_revenue']) }} XOF</h4>
                            <p class="text-muted mb-0">Revenus Totaux</p>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <h4 class="text-primary">{{ number_format($financial_stats['monthly_revenue']) }} XOF</h4>
                            <p class="text-muted mb-0">Revenus du Mois</p>
                        </div>
                        <div class="col-md-3 text-center border-end">
                            <h4 class="text-warning">{{ $financial_stats['pending_payments'] }}</h4>
                            <p class="text-muted mb-0">Paiements en Attente</p>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">{{ $financial_stats['collection_rate'] }}%</h4>
                            <p class="text-muted mb-0">Taux de Recouvrement</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Supervision -->
        <div class="col-lg-4 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-eye me-2"></i>
                        Supervision Système
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Connexions Aujourd'hui</span>
                            <strong>{{ $supervision_data['today_logins'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Utilisateurs Actifs</span>
                            <strong>{{ $supervision_data['active_users_week'] }}</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span>Activités Système</span>
                            <strong>{{ $supervision_data['system_activities'] }}</strong>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between">
                            <span>État Système</span>
                            <span class="badge bg-success">Opérationnel</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides et Activités Récentes -->
    <div class="row">
        <!-- Actions Rapides -->
        <div class="col-lg-4 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($quick_actions as $action)
                        @can($action['permission'])
                            <a href="{{ route($action['route']) }}" 
                               class="btn btn-outline-{{ $action['color'] }} btn-sm w-100 mb-2">
                                <i class="fas fa-{{ $action['icon'] }} me-2"></i>
                                {{ $action['title'] }}
                            </a>
                        @endcan
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Activités Récentes -->
        <div class="col-lg-8 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Activités Récentes
                    </h5>
                </div>
                <div class="card-body">
                    @if($recent_activities->isNotEmpty())
                        @foreach($recent_activities as $activity)
                            <div class="d-flex align-items-center mb-3">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-{{ $activity['icon'] }} text-muted"></i>
                                </div>
                                <div class="ms-3">
                                    <h6 class="mb-1">{{ $activity['title'] }}</h6>
                                    <p class="text-muted mb-1">{{ $activity['description'] }}</p>
                                    <small class="text-muted">{{ $activity['date']->diffForHumans() }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted mb-0">Aucune activité récente.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Tendance des inscriptions -->
    @if($enrollment_trend->isNotEmpty())
    <div class="row mt-4">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-area me-2"></i>
                        Évolution des Inscriptions (6 derniers mois)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        @foreach($enrollment_trend as $month)
                            <div class="col-2">
                                <h4 class="mb-1">{{ $month['count'] }}</h4>
                                <p class="text-muted small">{{ $month['month'] }}</p>
                            </div>
                        @endforeach
                    </div>
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