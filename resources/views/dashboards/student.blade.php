@extends('layouts.dashboard')

@section('title', 'Mon Espace Élève')

@section('content')
    <div class="space-y-6">
        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mon Espace Élève</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-2">Bienvenue {{ $student->user->name }}, suivez vos résultats et paiements</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center space-x-3">
                <span class="inline-flex items-center px-3 py-1.5 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 text-sm font-medium rounded-lg">
                    {{ $student->schoolClass->name ?? 'Non assigné' }}
                </span>
            </div>
        </div>

        <!-- Profile Quick Info -->
        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-2xl font-bold">{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">{{ $student->first_name }} {{ $student->last_name }}</h3>
                        <p class="text-blue-100">{{ $student->schoolClass->name ?? 'Non assigné' }}</p>
                        <p class="text-blue-100 text-sm">Matricule: {{ $student->student_id }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-blue-100">Année scolaire</p>
                    <p class="text-lg font-bold">{{ date('Y') }}-{{ date('Y') + 1 }}</p>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-dashboard.stat-card 
                title="Moyenne Générale"
                :value="number_format($generalAverage, 1)"
                color="blue"
                :trend="['type' => $averageTrend > 0 ? 'up' : 'down', 'value' => abs($averageTrend)]"
                description="Sur 20 points"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Matières"
                :value="$totalSubjects"
                color="green"
                description="Matières étudiées"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Rang en Classe"
                :value="ordinal($classRank)"
                color="purple"
                description="Sur {{ $totalClassStudents }} élèves"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Paiements"
                :value="$pendingPaymentsAmount > 0 ? number_format($pendingPaymentsAmount) . ' FCFA' : 'À jour'"
                color="orange"
                description="{{ $pendingPaymentsAmount > 0 ? 'En attente' : 'Tous les paiements effectués' }}"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z\'/></svg>'"
            />
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Grades Overview -->
            <div class="lg:col-span-2">
                <x-dashboard.card 
                    title="Mes Notes Récentes"
                    subtitle="Dernières évaluations et résultats"
                >
                    <x-slot name="actions">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Voir historique →
                        </a>
                    </x-slot>

                    <div class="space-y-4">
                        @forelse($recentGrades as $grade)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <div class="w-12 h-12 {{ $grade['grade'] >= 16 ? 'bg-green-100 text-green-600' : ($grade['grade'] >= 12 ? 'bg-yellow-100 text-yellow-600' : 'bg-red-100 text-red-600') }} rounded-lg flex items-center justify-center">
                                            <span class="font-bold text-sm">{{ $grade['grade'] }}</span>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $grade['subject'] }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $grade['evaluation_type'] }} • {{ $grade['date']->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-2xl font-bold {{ $grade['grade'] >= 16 ? 'text-green-600' : ($grade['grade'] >= 12 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $grade['grade'] }}/20
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            Coef. {{ $grade['coefficient'] }}
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                        <span>Classe: {{ number_format($grade['class_average'], 1) }}/20</span>
                                        <span>Rang: {{ $grade['rank'] }}/{{ $grade['total_students'] }}</span>
                                    </div>
                                    
                                    <div class="flex items-center">
                                        @if($grade['grade'] >= 16)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Excellent
                                            </span>
                                        @elseif($grade['grade'] >= 12)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                Bien
                                            </span>
                                        @elseif($grade['grade'] >= 10)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                Passable
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Insuffisant
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Progress bar for grade -->
                                <div class="mt-3">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div 
                                            class="h-2 rounded-full {{ $grade['grade'] >= 16 ? 'bg-green-500' : ($grade['grade'] >= 12 ? 'bg-yellow-500' : ($grade['grade'] >= 10 ? 'bg-blue-500' : 'bg-red-500')) }}"
                                            style="width: {{ ($grade['grade'] / 20) * 100 }}%"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <p class="text-gray-500 dark:text-gray-400">Aucune note disponible pour le moment</p>
                            </div>
                        @endforelse
                    </div>
                </x-dashboard.card>
            </div>

            <div class="space-y-6">
                <!-- Notifications -->
                <x-dashboard.card 
                    title="Notifications"
                    subtitle="Messages importants"
                >
                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                            <div class="flex items-start space-x-3">
                                <div class="flex-shrink-0 w-8 h-8 {{ $notification['type'] === 'payment' ? 'bg-red-100 text-red-600' : ($notification['type'] === 'grade' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600') }} rounded-full flex items-center justify-center">
                                    @if($notification['type'] === 'payment')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    @elseif($notification['type'] === 'grade')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">{{ $notification['title'] }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $notification['message'] }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                        {{ $notification['date']->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Voir toutes les notifications →
                            </a>
                        </div>
                    </div>
                </x-dashboard.card>

                <!-- Quick Access -->
                <x-dashboard.card 
                    title="Accès Rapide"
                    subtitle="Fonctions fréquentes"
                >
                    <div class="space-y-3">
                        <a href="#" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Mon bulletin</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Consulter mes résultats</p>
                            </div>
                        </a>

                        <a href="#" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Paiements</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Historique et factures</p>
                            </div>
                        </a>

                        <a href="#" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a4 4 0 118 0v4M5 21h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Emploi du temps</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Planning des cours</p>
                            </div>
                        </a>

                        <a href="#" class="w-full flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10m0 0V6a2 2 0 00-2-2H9a2 2 0 00-2 2v2m10 0v10a2 2 0 01-2 2H9a2 2 0 01-2-2V8m10 0H7"/>
                                </svg>
                            </div>
                            <div class="ml-3 text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Mes devoirs</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Travaux à rendre</p>
                            </div>
                        </a>
                    </div>
                </x-dashboard.card>
            </div>
        </div>

        <!-- Performance & Payments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Subject Performance -->
            <x-dashboard.card 
                title="Performance par Matière"
                subtitle="Vos moyennes dans chaque matière"
            >
                <div class="space-y-4">
                    @foreach($subjectAverages as $subject)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $subject['name'] }}</span>
                                <span class="text-sm {{ $subject['average'] >= 16 ? 'text-green-600' : ($subject['average'] >= 12 ? 'text-yellow-600' : ($subject['average'] >= 10 ? 'text-blue-600' : 'text-red-600')) }}">
                                    {{ number_format($subject['average'], 1) }}/20
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div 
                                    class="h-2 rounded-full {{ $subject['average'] >= 16 ? 'bg-green-500' : ($subject['average'] >= 12 ? 'bg-yellow-500' : ($subject['average'] >= 10 ? 'bg-blue-500' : 'bg-red-500')) }}"
                                    style="width: {{ ($subject['average'] / 20) * 100 }}%"
                                ></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>Coef. {{ $subject['coefficient'] }}</span>
                                <span>Rang: {{ $subject['rank'] }}/{{ $totalClassStudents }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </x-dashboard.card>

            <!-- Payment Status -->
            <x-dashboard.card 
                title="État des Paiements"
                subtitle="Historique et paiements en attente"
            >
                <div class="space-y-4">
                    @if($pendingPaymentsAmount > 0)
                        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-red-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-red-800 dark:text-red-400">Paiements en retard</h4>
                                    <p class="text-sm text-red-700 dark:text-red-400 mt-1">
                                        Montant dû: {{ number_format($pendingPaymentsAmount) }} FCFA
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex">
                                <svg class="w-5 h-5 text-green-400 mr-3 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-green-800 dark:text-green-400">Paiements à jour</h4>
                                    <p class="text-sm text-green-700 dark:text-green-400 mt-1">
                                        Tous vos paiements sont effectués
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Derniers paiements</h4>
                        @foreach($recentPayments as $payment)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $payment['description'] }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $payment['date']->format('d/m/Y') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($payment['amount']) }} FCFA</p>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $payment['status'] === 'paid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $payment['status'] === 'paid' ? 'Payé' : 'En attente' }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                        <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Voir l'historique complet →
                        </a>
                    </div>
                </div>
            </x-dashboard.card>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    // Animation pour les cartes de performance
    document.addEventListener('DOMContentLoaded', function() {
        const progressBars = document.querySelectorAll('[style*="width:"]');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
                bar.style.transition = 'width 1s ease-in-out';
            }, 100);
        });
    });
</script>
@endpush