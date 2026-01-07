@extends('layouts.dashboard')

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
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Planning d'Aujourd'hui -->
        <div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-4">
                    <h5 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-clock mr-2"></i>
                        Planning d'Aujourd'hui
                    </h5>
                </div>
                <div class="p-6">
                    @if(isset($schedule_today) && count($schedule_today) > 0)
                        <div class="space-y-4">
                            @foreach($schedule_today as $schedule)
                                <div class="border-l-4 {{ $schedule['is_current'] ? 'border-blue-500 bg-blue-50' : 'border-gray-300' }} p-4 rounded-r-lg">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h6 class="font-semibold text-gray-900">{{ $schedule['subject'] ?? 'Matière' }}</h6>
                                            <p class="text-gray-600 text-sm">
                                                {{ $schedule['class_name'] ?? 'Classe' }} • 
                                                {{ $schedule['room'] ?? 'Salle TBD' }}
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $schedule['time'] ?? '08h00' }}
                                            </span>
                                            @if($schedule['is_current'])
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-1">En cours</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">Aucun cours programmé aujourd'hui.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Mes Classes -->
        <div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-users mr-2 text-gray-600"></i>
                        Mes Classes
                    </h5>
                </div>
                <div class="p-6">
                    @if(isset($classes_assigned) && count($classes_assigned) > 0)
                        <div class="space-y-4">
                            @foreach($classes_assigned as $class)
                                <div class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h6 class="font-semibold text-gray-900">{{ $class['name'] ?? 'Classe' }}</h6>
                                            <p class="text-gray-600 text-sm">
                                                {{ $class['subject'] ?? 'Matière' }} • 
                                                {{ $class['students_count'] ?? 0 }} étudiant(s)
                                            </p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                                @switch($class['performance_level'] ?? 'bg-secondary')
                                                    @case('bg-success') bg-green-100 text-green-800 @break
                                                    @case('bg-warning') bg-yellow-100 text-yellow-800 @break
                                                    @case('bg-danger') bg-red-100 text-red-800 @break
                                                    @default bg-gray-100 text-gray-800
                                                @endswitch">
                                                {{ $class['performance_text'] ?? 'N/A' }}
                                            </span>
                                            <a href="{{ route('teacher.classes.show', $class['id'] ?? 1) }}" 
                                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-chalkboard text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">Aucune classe assignée.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Évaluations Récentes et Tâches Pendantes -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Évaluations Récentes -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-bar mr-2 text-gray-600"></i>
                        Dernières Évaluations
                    </h5>
                </div>
                <div class="p-6">
                    @if(isset($recent_evaluations) && count($recent_evaluations) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Évaluation</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classe</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Moyenne</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participation</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recent_evaluations as $evaluation)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $evaluation['title'] ?? 'Évaluation' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">{{ $evaluation['class'] ?? 'Classe' }}</td>
                                            <td class="px-4 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ ($evaluation['average'] ?? 0) >= 10 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                    {{ $evaluation['average'] ?? 'N/A' }}/20
                                                </span>
                                            </td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-600">{{ $evaluation['participation'] ?? 0 }}%</td>
                                            <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ $evaluation['date'] ?? now()->format('d/m') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-chart-line text-4xl text-gray-400 mb-3"></i>
                            <p class="text-gray-500">Aucune évaluation récente.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Tâches Pendantes -->
        <div>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden h-full">
                <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white px-6 py-4">
                    <h5 class="text-lg font-semibold flex items-center">
                        <i class="fas fa-tasks mr-2"></i>
                        Tâches à Accomplir
                    </h5>
                </div>
                <div class="p-6">
                    @if(isset($pending_tasks) && count($pending_tasks) > 0)
                        <div class="space-y-4">
                            @foreach($pending_tasks as $task)
                                <div class="border-l-4 {{ $task['priority_color'] === 'danger' ? 'border-red-500' : ($task['priority_color'] === 'warning' ? 'border-yellow-500' : 'border-blue-500') }} pl-4 py-3 bg-gray-50 rounded-r-lg">
                                    <h6 class="font-semibold text-gray-900 mb-1">{{ $task['title'] ?? 'Tâche' }}</h6>
                                    <p class="text-gray-600 text-sm mb-2">{{ $task['description'] ?? 'Description' }}</p>
                                    <div class="flex justify-between items-center">
                                        <small class="text-gray-500">{{ $task['deadline'] ?? 'Date limite TBD' }}</small>
                                        @if(isset($task['route']))
                                            <a href="{{ route($task['route']) }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Traiter
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                            <p class="text-green-600">Toutes les tâches sont à jour !</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Actions Rapides -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                Actions Rapides
            </h5>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                @can('manage_evaluations')
                <a href="{{ route('teacher.evaluations.create') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors group">
                    <i class="fas fa-plus-circle text-4xl text-blue-500 group-hover:text-blue-600 mb-3"></i>
                    <span class="text-blue-700 group-hover:text-blue-800 font-medium">Nouvelle Évaluation</span>
                </a>
                @endcan
                
                @can('view_student_grades')
                <a href="{{ route('teacher.grades.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-green-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors group">
                    <i class="fas fa-clipboard-list text-4xl text-green-500 group-hover:text-green-600 mb-3"></i>
                    <span class="text-green-700 group-hover:text-green-800 font-medium">Saisie des Notes</span>
                </a>
                @endcan
                
                @can('view_schedule')
                <a href="{{ route('teacher.schedule.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-cyan-300 rounded-lg hover:border-cyan-500 hover:bg-cyan-50 transition-colors group">
                    <i class="fas fa-calendar text-4xl text-cyan-500 group-hover:text-cyan-600 mb-3"></i>
                    <span class="text-cyan-700 group-hover:text-cyan-800 font-medium">Mon Planning</span>
                </a>
                @endcan
                
                @can('manage_classes')
                <a href="{{ route('teacher.classes.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-yellow-300 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors group">
                    <i class="fas fa-users text-4xl text-yellow-500 group-hover:text-yellow-600 mb-3"></i>
                    <span class="text-yellow-700 group-hover:text-yellow-800 font-medium">Gestion Classes</span>
                </a>
                @endcan
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
        
        // TODO: Logique pour marquer le cours actuel avec Tailwind CSS
        
        console.log('Dashboard teacher - mise à jour du planning...', currentTime);
    }

    // Actualisation toutes les minutes
    setInterval(updateCurrentClass, 60000);

    // Animation au chargement
    $(document).ready(function() {
        // Animation des cartes au chargement
        $('.transform').each(function(index) {
            $(this).delay(index * 100).queue(function() {
                $(this).addClass('animate-fadeInUp').dequeue();
            });
        });
    });
</script>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 20px, 0);
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.5s ease-out forwards;
}
</style>
@endpush