@extends('layouts.dashboard')

@section('title', 'Dashboard Étudiant Universitaire')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête Dashboard -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-1">Dashboard Étudiant</h2>
                <p class="text-gray-600">
                    Parcours Universitaire • 
                    <span class="font-semibold">{{ $student_profile->user->name ?? 'Étudiant' }}</span> •
                    Semestre {{ $academic_path['current_semester'] ?? 'N/A' }}
                </p>
            </div>
            <div class="mt-3 sm:mt-0 flex space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $academic_path['specialization'] ?? 'Spécialisation' }}
                </span>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    {{ $academic_path['credits_earned'] ?? 0 }} crédits
                </span>
            </div>
        </div>
    </div>

    <!-- Indicateurs de Performance -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium mb-1">Moyenne Générale</p>
                    <h3 class="text-2xl font-bold">{{ $grades_summary['general_average'] ?? '--' }}/20</h3>
                    <p class="text-purple-100 text-xs">
                        @if(isset($grades_summary['trend']) && $grades_summary['trend'] > 0)
                            <i class="fas fa-arrow-up"></i> +{{ $grades_summary['trend'] }}
                        @elseif(isset($grades_summary['trend']) && $grades_summary['trend'] < 0)
                            <i class="fas fa-arrow-down"></i> {{ $grades_summary['trend'] }}
                        @else
                            <i class="fas fa-minus"></i> Stable
                        @endif
                    </p>
                </div>
                <i class="fas fa-graduation-cap text-4xl text-purple-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium mb-1">UE Validées</p>
                    <h3 class="text-2xl font-bold">{{ $academic_path['validated_ue'] ?? 0 }}/{{ $academic_path['total_ue'] ?? 0 }}</h3>
                    <p class="text-blue-100 text-xs">{{ round((($academic_path['validated_ue'] ?? 0) / max($academic_path['total_ue'] ?? 1, 1)) * 100) }}% de progression</p>
                </div>
                <i class="fas fa-check-circle text-4xl text-blue-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium mb-1">Crédits Acquis</p>
                    <h3 class="text-2xl font-bold">{{ $academic_path['credits_earned'] ?? 0 }}</h3>
                    <p class="text-green-100 text-xs">sur {{ $academic_path['credits_required'] ?? 0 }} requis</p>
                </div>
                <i class="fas fa-medal text-4xl text-green-200"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white transform hover:-translate-y-1 transition-transform duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium mb-1">Rang de Classe</p>
                    <h3 class="text-2xl font-bold">{{ $grades_summary['class_rank'] ?? '--' }}</h3>
                    <p class="text-orange-100 text-xs">sur {{ $grades_summary['total_students'] ?? 0 }} étudiants</p>
                </div>
                <i class="fas fa-trophy text-4xl text-orange-200"></i>
            </div>
        </div>
    </div>

    <!-- UE du Semestre en Cours et Progression -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- UE du Semestre -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 text-white px-6 py-4">
                <h5 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-book-open mr-2"></i>
                    Unités d'Enseignement - Semestre {{ $academic_path['current_semester'] ?? 'N/A' }}
                </h5>
            </div>
            <div class="p-6">
                @if(isset($current_ue) && count($current_ue) > 0)
                    <div class="space-y-4">
                        @foreach($current_ue as $ue)
                            <div class="border-l-4 @if(($ue['average'] ?? 0) >= 10) border-green-500 @else border-red-500 @endif bg-gray-50 rounded-r-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h6 class="font-semibold text-gray-900 mb-1">{{ $ue['name'] ?? 'UE' }}</h6>
                                        <p class="text-gray-600 text-sm mb-2">{{ $ue['code'] ?? '' }} • {{ $ue['credits'] ?? 0 }} crédits</p>
                                        <p class="text-gray-500 text-sm">Enseignant: {{ $ue['teacher'] ?? 'Non assigné' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold @if(($ue['average'] ?? 0) >= 10) text-green-600 @else text-red-600 @endif">
                                            {{ $ue['average'] ?? '--' }}/20
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            Coef. {{ $ue['coefficient'] ?? 1 }}
                                        </div>
                                        <span class="inline-block mt-1 px-2 py-1 text-xs rounded-full @if(($ue['average'] ?? 0) >= 10) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                            @if(($ue['average'] ?? 0) >= 10) Validée @else En cours @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-book text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Aucune UE inscrite ce semestre.</p>
                        <a href="{{ route('student.university.enrollment.index') }}" 
                           class="mt-3 inline-flex items-center px-4 py-2 border border-blue-500 text-blue-700 rounded hover:bg-blue-50 transition-colors">
                            S'inscrire aux UE
                        </a>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Progression Académique -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-chart-line mr-2 text-gray-600"></i>
                    Progression du Cursus
                </h5>
            </div>
            <div class="p-6">
                <div class="space-y-6">
                    <!-- Progression par année -->
                    @if(isset($academic_progress))
                        @foreach($academic_progress as $year => $progress)
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700">{{ $year }}ème année</span>
                                    <span class="text-sm text-gray-500">{{ $progress['percentage'] ?? 0 }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3">
                                    <div class="bg-blue-600 h-3 rounded-full" style="width: {{ $progress['percentage'] ?? 0 }}%"></div>
                                </div>
                                <div class="mt-1 text-xs text-gray-500">
                                    {{ $progress['credits_earned'] ?? 0 }}/{{ $progress['credits_required'] ?? 0 }} crédits
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500 text-sm">Données de progression non disponibles</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Évaluations Récentes et Planning -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Évaluations Récentes -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-clipboard-list mr-2 text-gray-600"></i>
                    Évaluations Récentes
                </h5>
            </div>
            <div class="p-6">
                @if(isset($recent_evaluations) && count($recent_evaluations) > 0)
                    <div class="space-y-4">
                        @foreach($recent_evaluations as $eval)
                            <div class="flex justify-between items-center p-3 border rounded-lg">
                                <div>
                                    <h6 class="font-medium text-gray-900">{{ $eval['ue_name'] ?? 'UE' }}</h6>
                                    <p class="text-sm text-gray-600">{{ $eval['type'] ?? 'Évaluation' }} • {{ $eval['date'] ?? 'Date TBD' }}</p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold @if(($eval['grade'] ?? 0) >= 10) text-green-600 @else text-red-600 @endif">
                                        {{ $eval['grade'] ?? '--' }}/20
                                    </div>
                                    <div class="text-xs text-gray-500">Coef. {{ $eval['coefficient'] ?? 1 }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-chart-bar text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Aucune évaluation récente.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Planning de la Semaine -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="border-b border-gray-200 px-6 py-4">
                <h5 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-alt mr-2 text-gray-600"></i>
                    Planning de la Semaine
                </h5>
            </div>
            <div class="p-6">
                @if(isset($weekly_schedule) && count($weekly_schedule) > 0)
                    <div class="space-y-3">
                        @foreach($weekly_schedule as $schedule)
                            <div class="border-l-4 @if($schedule['is_today']) border-blue-500 bg-blue-50 @else border-gray-300 @endif pl-4 py-2">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h6 class="font-medium text-gray-900">{{ $schedule['ue_name'] ?? 'UE' }}</h6>
                                        <p class="text-sm text-gray-600">{{ $schedule['teacher'] ?? 'Enseignant' }} • {{ $schedule['room'] ?? 'Salle TBD' }}</p>
                                    </div>
                                    <div class="text-right text-sm">
                                        <div class="font-medium text-gray-700">{{ $schedule['time'] ?? '08h00' }}</div>
                                        <div class="text-gray-500">{{ $schedule['day'] ?? 'Lundi' }}</div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <i class="fas fa-calendar-times text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Aucun cours programmé cette semaine.</p>
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
                <a href="{{ route('student.university.grades.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-purple-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors group">
                    <i class="fas fa-chart-line text-3xl text-purple-500 mb-3 group-hover:text-purple-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-purple-600">Consulter Notes</span>
                </a>
                @endcan
                
                @can('manage_enrollment')
                <a href="{{ route('student.university.enrollment.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors group">
                    <i class="fas fa-edit text-3xl text-blue-500 mb-3 group-hover:text-blue-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-blue-600">Inscription UE</span>
                </a>
                @endcan
                
                @can('view_own_documents')
                <a href="{{ route('student.university.documents.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-green-300 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors group">
                    <i class="fas fa-file-alt text-3xl text-green-500 mb-3 group-hover:text-green-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-green-600">Documents</span>
                </a>
                @endcan
                
                @can('view_schedule')
                <a href="{{ route('student.university.calendar.index') }}" 
                   class="flex flex-col items-center justify-center p-6 border-2 border-dashed border-indigo-300 rounded-lg hover:border-indigo-500 hover:bg-indigo-50 transition-colors group">
                    <i class="fas fa-calendar text-3xl text-indigo-500 mb-3 group-hover:text-indigo-600"></i>
                    <span class="text-center font-medium text-gray-700 group-hover:text-indigo-600">Calendrier</span>
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Animation au chargement des cartes
    $(document).ready(function() {
        $('.transform').each(function(index) {
            $(this).delay(index * 100).queue(function() {
                $(this).addClass('animate-fadeInUp').dequeue();
            });
        });
    });

    // Mise à jour de la progression en temps réel
    function updateProgress() {
        console.log('Dashboard étudiant universitaire - mise à jour de la progression...');
        // TODO: Implémenter actualisation AJAX des données
    }

    setInterval(updateProgress, 300000); // Chaque 5 minutes
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