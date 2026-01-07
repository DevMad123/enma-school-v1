@extends('layouts.dashboard')

@section('title', 'Dashboard Étudiant')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête Dashboard -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Dashboard Étudiant</h2>
                <p class="text-gray-600">
                    Parcours Scolaire • 
                    <span class="font-semibold">{{ $student_profile->user->name ?? 'Étudiant' }}</span> •
                    Classe {{ $current_class->name ?? 'Non assignée' }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $current_class->level->name ?? 'Niveau' }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                    @if(($bulletin_summary['general_average'] ?? 0) >= 10) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                    {{ $bulletin_summary['general_average'] ?? '--' }}/20
                </span>
            </div>
        </div>
    </div>

    <!-- Indicateurs de Performance Scolaire -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">Moyenne Générale</p>
                    <h3 class="text-2xl font-bold">{{ $bulletin_summary['general_average'] ?? '--' }}/20</h3>
                    <p class="text-blue-100 text-xs">
                        @if(isset($bulletin_summary['trend']) && $bulletin_summary['trend'] > 0)
                            <i class="fas fa-arrow-up"></i> +{{ number_format($bulletin_summary['trend'], 1) }}
                        @elseif(isset($bulletin_summary['trend']) && $bulletin_summary['trend'] < 0)
                            <i class="fas fa-arrow-down"></i> {{ number_format($bulletin_summary['trend'], 1) }}
                        @else
                            <i class="fas fa-minus"></i> Stable
                        @endif
                    </p>
                </div>
                <i class="fas fa-chart-line text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Rang de Classe</p>
                    <h3 class="text-2xl font-bold">{{ $bulletin_summary['class_rank'] ?? '--' }}</h3>
                    <p class="text-purple-100 text-xs">sur {{ $bulletin_summary['total_students'] ?? 0 }} élèves</p>
                </div>
                <i class="fas fa-trophy text-4xl text-purple-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Présence</p>
                    <h3 class="text-2xl font-bold">{{ $school_life_summary['attendance_rate'] ?? '--' }}%</h3>
                    <p class="text-green-100 text-xs">{{ $school_life_summary['present_days'] ?? 0 }}/{{ $school_life_summary['school_days'] ?? 0 }} jours</p>
                </div>
                <i class="fas fa-user-check text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Devoirs Rendus</p>
                    <h3 class="text-2xl font-bold">{{ $homework_summary['completed'] ?? 0 }}/{{ $homework_summary['total'] ?? 0 }}</h3>
                    <p class="text-orange-100 text-xs">{{ round((($homework_summary['completed'] ?? 0) / max($homework_summary['total'] ?? 1, 1)) * 100) }}% réalisés</p>
                </div>
                <i class="fas fa-tasks text-4xl text-orange-200"></i>
            </div>
        </div>
    </div>

    <!-- Bulletin et Matières -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Moyennes par Matière -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4">
                <h5 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-book mr-2"></i>
                    Moyennes par Matière - {{ $bulletin_summary['period'] ?? 'Trimestre en cours' }}
                </h5>
            </div>
            <div class="p-6">
                @if(isset($subject_averages) && count($subject_averages) > 0)
                    <div class="space-y-4">
                        @foreach($subject_averages as $subject)
                            <div class="border-l-4 @if(($subject['average'] ?? 0) >= 10) border-green-500 @else border-red-500 @endif bg-gray-50 rounded-r-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-gray-900 mb-1">{{ $subject['name'] ?? 'Matière' }}</h6>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                                            <span>Coef. {{ $subject['coefficient'] ?? 1 }}</span>
                                            <span>Rang: {{ $subject['rank'] ?? '--' }}/{{ $subject['total_students'] ?? 0 }}</span>
                                            <span>Prof: {{ $subject['teacher'] ?? 'Non assigné' }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xl font-bold @if(($subject['average'] ?? 0) >= 10) text-green-600 @else text-red-600 @endif">
                                            {{ $subject['average'] ?? '--' }}/20
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Classe: {{ $subject['class_average'] ?? '--' }}
                                        </div>
                                        <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full @if(($subject['average'] ?? 0) >= 10) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                            @if(($subject['average'] ?? 0) >= 16) Excellent
                                            @elseif(($subject['average'] ?? 0) >= 14) Bien
                                            @elseif(($subject['average'] ?? 0) >= 12) Assez Bien
                                            @elseif(($subject['average'] ?? 0) >= 10) Passable
                                            @else Insuffisant
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-book-open text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Aucune note disponible pour ce trimestre.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Appréciation et Conseils -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-comment-alt mr-2 text-gray-600"></i>
                    Appréciations
                </h5>
            </div>
            <div class="p-6">
                @if(isset($appreciations) && count($appreciations) > 0)
                    <div class="space-y-4">
                        @foreach($appreciations as $appreciation)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex items-start">
                                    <i class="fas fa-user-tie text-blue-500 mt-1 mr-3"></i>
                                    <div class="flex-1">
                                        <h6 class="font-medium text-blue-900">{{ $appreciation['teacher'] ?? 'Professeur Principal' }}</h6>
                                        <p class="text-blue-800 text-sm mt-1">{{ $appreciation['subject'] ?? 'Appréciation générale' }}</p>
                                        <blockquote class="text-blue-700 text-sm italic mt-2 border-l-2 border-blue-300 pl-3">
                                            "{{ $appreciation['comment'] ?? 'Bon travail, continuez ainsi.' }}"
                                        </blockquote>
                                        <p class="text-blue-600 text-xs mt-2">{{ $appreciation['date'] ?? now()->format('d/m/Y') }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <i class="fas fa-comment text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500 text-sm">Aucune appréciation disponible.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Devoirs et Planning -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Devoirs à Rendre -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list mr-2 text-gray-600"></i>
                    Devoirs à Rendre
                </h5>
            </div>
            <div class="p-6">
                @if(isset($upcoming_homework) && count($upcoming_homework) > 0)
                    <div class="space-y-3">
                        @foreach($upcoming_homework as $homework)
                            <div class="flex justify-between items-center p-3 border rounded-lg @if($homework['priority'] === 'urgent') border-red-300 bg-red-50 @elseif($homework['priority'] === 'important') border-yellow-300 bg-yellow-50 @else border-gray-200 @endif">
                                <div>
                                    <h6 class="font-medium text-gray-900">{{ $homework['subject'] ?? 'Matière' }}</h6>
                                    <p class="text-sm text-gray-600">{{ $homework['title'] ?? 'Devoir' }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ $homework['description'] ?? 'Description du devoir' }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-medium @if($homework['priority'] === 'urgent') text-red-600 @elseif($homework['priority'] === 'important') text-yellow-600 @else text-gray-600 @endif">
                                        {{ $homework['due_date'] ?? 'Date limite TBD' }}
                                    </div>
                                    <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full @if($homework['status'] === 'completed') bg-green-100 text-green-800 @elseif($homework['priority'] === 'urgent') bg-red-100 text-red-800 @else bg-yellow-100 text-yellow-800 @endif">
                                        {{ ucfirst($homework['status'] ?? 'à faire') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-check-circle text-4xl text-green-400 mb-3"></i>
                        <p class="text-green-600 font-medium">Tous les devoirs sont à jour !</p>
                        <p class="text-gray-500 text-sm">Excellent travail.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Emploi du Temps -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-gray-600"></i>
                    Emploi du Temps - {{ now()->format('d/m/Y') }}
                </h5>
            </div>
            <div class="p-6">
                @if(isset($daily_schedule) && count($daily_schedule) > 0)
                    <div class="space-y-3">
                        @foreach($daily_schedule as $schedule)
                            <div class="border-l-4 @if($schedule['is_current']) border-blue-500 bg-blue-50 @elseif($schedule['is_past']) border-gray-300 bg-gray-50 @else border-green-500 @endif pl-4 py-2">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h6 class="font-medium @if($schedule['is_current']) text-blue-900 @elseif($schedule['is_past']) text-gray-600 @else text-gray-900 @endif">
                                            {{ $schedule['subject'] ?? 'Matière' }}
                                        </h6>
                                        <p class="text-sm @if($schedule['is_current']) text-blue-700 @elseif($schedule['is_past']) text-gray-500 @else text-gray-600 @endif">
                                            {{ $schedule['teacher'] ?? 'Professeur' }} • {{ $schedule['room'] ?? 'Salle TBD' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-medium @if($schedule['is_current']) text-blue-600 @elseif($schedule['is_past']) text-gray-400 @else text-gray-700 @endif">
                                            {{ $schedule['time'] ?? '08h00-09h00' }}
                                        </div>
                                        @if($schedule['is_current'])
                                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">En cours</span>
                                        @elseif($schedule['is_past'])
                                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Terminé</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Aucun cours programmé aujourd'hui.</p>
                    </div>
                @endif
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
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @can('view_own_grades')
                <a href="{{ route('student.preuniversity.bulletin.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors group">
                    <i class="fas fa-chart-bar text-3xl text-blue-500 mb-3 group-hover:text-blue-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-blue-600">Mon Bulletin</span>
                </a>
                @endcan
                
                @can('view_homework')
                <a href="{{ route('student.preuniversity.homework.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-orange-300 rounded-lg hover:border-orange-500 hover:bg-orange-50 transition-colors group">
                    <i class="fas fa-tasks text-3xl text-orange-500 mb-3 group-hover:text-orange-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-orange-600">Mes Devoirs</span>
                </a>
                @endcan
                
                @can('view_schedule')
                <a href="{{ route('student.preuniversity.schedule.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-green-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors group">
                    <i class="fas fa-calendar text-3xl text-green-500 mb-3 group-hover:text-green-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-green-600">Emploi du Temps</span>
                </a>
                @endcan
                
                @can('view_school_life')
                <a href="{{ route('student.preuniversity.school-life.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-purple-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors group">
                    <i class="fas fa-school text-3xl text-purple-500 mb-3 group-hover:text-purple-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-purple-600">Vie Scolaire</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Animation au chargement
    $(document).ready(function() {
        $('.transform').each(function(index) {
            $(this).delay(index * 100).queue(function() {
                $(this).addClass('animate-fadeInUp').dequeue();
            });
        });
    });

    // Mise à jour de l'emploi du temps en temps réel
    function updateCurrentClass() {
        const now = new Date();
        $('.border-l-4').removeClass('border-blue-500 bg-blue-50').addClass('border-green-500');
        // TODO: Logique pour marquer le cours actuel
    }

    setInterval(updateCurrentClass, 60000); // Chaque minute
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