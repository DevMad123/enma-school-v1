@extends('layouts.app')

@section('title', 'Dashboard Enseignant')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête Dashboard -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Dashboard Enseignant</h2>
                <p class="text-gray-600">
                    Espace pédagogique • 
                    <span class="font-semibold">{{ $teacher_profile->user->name ?? 'Enseignant' }}</span> •
                    {{ now()->format('l d F Y') }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ $classes_assigned ? count($classes_assigned) : 0 }} classe(s) assignée(s)
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques d'aperçu -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Classes Assignées</p>
                    <h3 class="text-2xl font-bold">{{ count($classes_assigned ?? []) }}</h3>
                </div>
                <i class="fas fa-home text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Total Étudiants</p>
                    <h3 class="text-2xl font-bold">{{ $student_stats['total_students'] ?? 0 }}</h3>
                </div>
                <i class="fas fa-user-graduate text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-cyan-500 to-cyan-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-cyan-100 text-sm font-medium mb-1">Cours cette Semaine</p>
                    <h3 class="text-2xl font-bold">{{ $student_stats['weekly_classes'] ?? 0 }}</h3>
                </div>
                <i class="fas fa-calendar-week text-4xl text-cyan-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Évaluations en Attente</p>
                    <h3 class="text-2xl font-bold">{{ count($pending_tasks ?? []) }}</h3>
                </div>
                <i class="fas fa-clipboard-check text-4xl text-orange-200"></i>
            </div>
        </div>
    </div>

    <!-- Planning du Jour et Classes -->
    <div class="row mb-4">
        <!-- Planning d'Aujourd'hui -->
        <div class="col-lg-6 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-clock me-2"></i>
                        Planning d'Aujourd'hui
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($schedule_today) && count($schedule_today) > 0)
                        @foreach($schedule_today as $schedule)
                            <div class="schedule-item {{ $schedule['is_current'] ? 'current' : '' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $schedule['subject'] ?? 'Matière' }}</h6>
                                        <p class="text-muted mb-0">
                                            {{ $schedule['class_name'] ?? 'Classe' }} • 
                                            {{ $schedule['room'] ?? 'Salle TBD' }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-secondary">
                                            {{ $schedule['time'] ?? '08h00' }}
                                        </span>
                                        @if($schedule['is_current'])
                                            <span class="badge bg-danger ms-1">En cours</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucun cours programmé aujourd'hui.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Mes Classes -->
        <div class="col-lg-6 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Mes Classes
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($classes_assigned) && count($classes_assigned) > 0)
                        @foreach($classes_assigned as $class)
                            <div class="card class-card mb-3">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $class['name'] ?? 'Classe' }}</h6>
                                            <p class="text-muted small mb-0">
                                                {{ $class['subject'] ?? 'Matière' }} • 
                                                {{ $class['students_count'] ?? 0 }} étudiant(s)
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge performance-badge {{ $class['performance_level'] ?? 'bg-secondary' }}">
                                                {{ $class['performance_text'] ?? 'N/A' }}
                                            </span>
                                            <a href="{{ route('teacher.classes.show', $class['id'] ?? 1) }}" 
                                               class="btn btn-sm btn-outline-primary ms-2">
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chalkboard fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune classe assignée.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Évaluations Récentes et Tâches Pendantes -->
    <div class="row mb-4">
        <!-- Évaluations Récentes -->
        <div class="col-lg-7 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Dernières Évaluations
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($recent_evaluations) && count($recent_evaluations) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Évaluation</th>
                                        <th>Classe</th>
                                        <th>Moyenne</th>
                                        <th>Participation</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_evaluations as $evaluation)
                                        <tr>
                                            <td>{{ $evaluation['title'] ?? 'Évaluation' }}</td>
                                            <td>{{ $evaluation['class'] ?? 'Classe' }}</td>
                                            <td>
                                                <span class="badge {{ $evaluation['average'] >= 10 ? 'bg-success' : 'bg-warning' }}">
                                                    {{ $evaluation['average'] ?? 'N/A' }}/20
                                                </span>
                                            </td>
                                            <td>{{ $evaluation['participation'] ?? 0 }}%</td>
                                            <td class="text-muted">{{ $evaluation['date'] ?? now()->format('d/m') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Aucune évaluation récente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Tâches Pendantes -->
        <div class="col-lg-5 mb-3">
            <div class="card dashboard-card h-100">
                <div class="card-header bg-warning">
                    <h5 class="mb-0 text-dark">
                        <i class="fas fa-tasks me-2"></i>
                        Tâches à Accomplir
                    </h5>
                </div>
                <div class="card-body">
                    @if(isset($pending_tasks) && count($pending_tasks) > 0)
                        @foreach($pending_tasks as $task)
                            <div class="border-start border-{{ $task['priority_color'] ?? 'secondary' }} border-3 ps-3 mb-3">
                                <h6 class="mb-1">{{ $task['title'] ?? 'Tâche' }}</h6>
                                <p class="text-muted small mb-1">{{ $task['description'] ?? 'Description' }}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $task['deadline'] ?? 'Date limite TBD' }}</small>
                                    @if(isset($task['route']))
                                        <a href="{{ route($task['route']) }}" class="btn btn-sm btn-outline-primary">
                                            Traiter
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-success">Toutes les tâches sont à jour !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="row">
        <div class="col-12">
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Actions Rapides
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @can('manage_evaluations')
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('teacher.evaluations.create') }}" class="btn btn-outline-primary w-100 h-100">
                                <i class="fas fa-plus-circle d-block mb-2 fa-2x"></i>
                                <span class="d-block">Nouvelle Évaluation</span>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_student_grades')
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('teacher.grades.index') }}" class="btn btn-outline-success w-100 h-100">
                                <i class="fas fa-clipboard-list d-block mb-2 fa-2x"></i>
                                <span class="d-block">Saisie des Notes</span>
                            </a>
                        </div>
                        @endcan
                        
                        @can('view_schedule')
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('teacher.schedule.index') }}" class="btn btn-outline-info w-100 h-100">
                                <i class="fas fa-calendar d-block mb-2 fa-2x"></i>
                                <span class="d-block">Mon Planning</span>
                            </a>
                        </div>
                        @endcan
                        
                        @can('manage_classes')
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('teacher.classes.index') }}" class="btn btn-outline-warning w-100 h-100">
                                <i class="fas fa-users d-block mb-2 fa-2x"></i>
                                <span class="d-block">Gestion Classes</span>
                            </a>
                        </div>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Mise à jour du planning en temps réel
    function updateCurrentClass() {
        const now = new Date();
        const currentTime = now.getHours() + ':' + String(now.getMinutes()).padStart(2, '0');
        
        $('.schedule-item').removeClass('current');
        // TODO: Logique pour marquer le cours actuel
        
        console.log('Dashboard teacher - mise à jour du planning...', currentTime);
    }

    // Actualisation toutes les minutes
    setInterval(updateCurrentClass, 60000);

    // Animation au chargement
    $(document).ready(function() {
        $('.dashboard-card').hide().fadeIn(500);
        
        // Tooltip pour les badges de performance
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
@endpush