@extends('layouts.dashboard')

@section('title', 'Dashboard Administration')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- En-tête Dashboard -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-1">Dashboard Administration</h1>
                <p class="text-gray-600 dark:text-gray-400">
                    Vue d'ensemble et pilotage de l'établissement • 
                    <span class="font-semibold">{{ $overview_stats['school_context'] ?? 'ENMA School' }}</span>
                </p>
            </div>
            <div class="mt-3 sm:mt-0 text-right">
                <div class="text-gray-500 text-sm">Dernière mise à jour</div>
                <div class="font-bold text-gray-900 dark:text-white">{{ now()->format('d/m/Y H:i') }}</div>
            </div>
        </div>
    </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-dashboard.stat-card 
                title="Total Étudiants"
                :value="number_format($totalStudents)"
                color="blue"
                :trend="['type' => 'up', 'value' => '+12%']"
                description="Par rapport au mois dernier"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Enseignants"
                :value="number_format($totalTeachers)"
                color="green"
                :trend="['type' => 'up', 'value' => '+3']"
                description="Nouveaux ce mois"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Classes Actives"
                :value="number_format($totalClasses)"
                color="purple"
                description="Toutes sections confondues"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2\'/></svg>'"
            />

            <x-dashboard.stat-card 
                title="Revenus ce mois"
                :value="number_format($monthlyRevenue, 0, ',', ' ') . ' FCFA'"
                color="green"
                :trend="['type' => 'up', 'value' => '+15%']"
                description="Objectif: 85% atteint"
                :icon="'<svg class=\'w-6 h-6 text-white\' fill=\'none\' stroke=\'currentColor\' viewBox=\'0 0 24 24\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1\'/></svg>'"
                :link="route('finance.index')"
            />
        </div>

        <!-- Financial Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-dashboard.card 
                    title="Évolution des Revenus"
                    subtitle="Tendances des 6 derniers mois"
                >
                    <x-slot name="actions">
                        <select class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1 bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                            <option>6 mois</option>
                            <option>12 mois</option>
                            <option>Cette année</option>
                        </select>
                    </x-slot>

                    <div class="space-y-4">
                        <!-- Simple chart simulation with CSS -->
                        <div class="flex items-end space-x-2 h-40">
                            @foreach($enrollmentTrend as $month)
                                <div class="flex-1 flex flex-col items-center">
                                    <div 
                                        class="w-full bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-sm transition-all duration-500 hover:from-blue-600 hover:to-blue-500"
                                        style="height: {{ ($month['count'] / $enrollmentTrend->max('count')) * 100 }}%"
                                        title="{{ $month['count'] }} inscriptions"
                                    ></div>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ $month['month'] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Stats summary -->
                        <div class="grid grid-cols-3 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalRevenue, 0, ',', ' ') }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Revenus totaux (FCFA)</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-orange-600">{{ $pendingPayments }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Paiements en attente</p>
                            </div>
                            <div class="text-center">
                                <p class="text-2xl font-bold text-red-600">{{ $overduePayments }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Paiements en retard</p>
                            </div>
                        </div>
                    </div>
                </x-dashboard.card>
            </div>

            <div>
                <x-dashboard.card 
                    title="Actions Rapides"
                    subtitle="Raccourcis fréquents"
                >
                    <div class="space-y-3">
                        <a href="{{ route('finance.school-fees.create') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Créer frais scolaire</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Nouveau type de frais</p>
                            </div>
                        </a>

                        <a href="{{ route('finance.payments.create') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Enregistrer paiement</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Nouveau paiement</p>
                            </div>
                        </a>

                        <a href="#" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 text-purple-600 dark:text-purple-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Nouvel étudiant</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Inscription rapide</p>
                            </div>
                        </a>

                        <a href="{{ route('finance.reports') }}" class="flex items-center p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors duration-200 group">
                            <div class="w-10 h-10 bg-indigo-100 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center group-hover:scale-105 transition-transform duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Générer rapport</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Rapports financiers</p>
                            </div>
                        </a>
                    </div>
                </x-dashboard.card>
            </div>
        </div>

        <!-- Recent Payments Table -->
        <x-dashboard.card 
            title="Derniers Paiements"
            subtitle="Activité financière récente"
            :noPadding="true"
        >
            <x-slot name="actions">
                <a href="{{ route('finance.payments.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    Voir tous →
                </a>
            </x-slot>

            @php
                $headers = ['Étudiant', 'Frais', 'Montant', 'Méthode', 'Date', 'Statut'];
                
                $rows = $recentPayments->map(function($payment) {
                    return [
                        '<div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                                <span class="text-xs font-medium text-gray-600">' . substr($payment->student->user->name ?? 'N/A', 0, 1) . '</span>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">' . ($payment->student->user->name ?? 'N/A') . '</span>
                        </div>',
                        
                        '<span class="text-gray-900 dark:text-white">' . ($payment->schoolFee->name ?? 'Frais supprimé') . '</span>',
                        
                        '<span class="font-medium text-gray-900 dark:text-white">' . number_format($payment->amount, 0, ',', ' ') . ' FCFA</span>',
                        
                        '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . 
                        ($payment->payment_method === 'cash' ? 'bg-green-100 text-green-800' : 
                         ($payment->payment_method === 'bank_transfer' ? 'bg-blue-100 text-blue-800' : 
                          ($payment->payment_method === 'mobile_money' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800'))) . '">' . 
                        ucfirst($payment->payment_method) . '</span>',
                        
                        '<span class="text-gray-500 dark:text-gray-400">' . $payment->created_at->format('d/m/Y') . '</span>',
                        
                        '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ' . 
                        ($payment->status === 'confirmed' ? 'bg-green-100 text-green-800' : 
                         ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) . '">' . 
                        ucfirst($payment->status) . '</span>'
                    ];
                })->toArray();
            @endphp

            <x-dashboard.table 
                :headers="$headers" 
                :rows="$rows"
                empty="Aucun paiement récent"
            />
        </x-dashboard.card>

        <!-- Activity Summary -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-dashboard.card 
                title="Activité de la semaine"
                subtitle="Nouvelles inscriptions et activités"
            >
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Nouveaux étudiants</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">+{{ $recentActivities['new_students'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Nouveaux enseignants</span>
                        <span class="text-sm font-medium text-gray-900 dark:text-white">+{{ $recentActivities['new_teachers'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Total affectations</span>
                        <span class="text-sm font-medium text-blue-600">{{ $recentActivities['total_assignments'] }}</span>
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card 
                title="Alertes & Notifications"
                subtitle="Points d'attention"
            >
                <div class="space-y-3">
                    @if($pendingPayments > 0)
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-orange-500 rounded-full mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $pendingPayments }} paiements en attente</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Nécessitent une confirmation</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($overduePayments > 0)
                        <div class="flex items-start space-x-3">
                            <div class="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $overduePayments }} frais en retard</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Échéances dépassées</p>
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Système opérationnel</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Tout fonctionne normalement</p>
                        </div>
                    </div>
                </div>
            </x-dashboard.card>

            <x-dashboard.card 
                title="Performance"
                subtitle="Indicateurs clés"
            >
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Taux de paiement</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">85%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Occupation classes</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">92%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full" style="width: 92%"></div>
                        </div>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Satisfaction</span>
                            <span class="text-sm font-medium text-gray-900 dark:text-white">96%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-purple-500 h-2 rounded-full" style="width: 96%"></div>
                        </div>
                    </div>
                </div>
            </x-dashboard.card>
        </div>

        <!-- MODULE A3 - Structure Académique (Conditionnement selon le type d'école) -->
        @php
            $school = \App\Models\School::getActiveSchool();
        @endphp
        
        @if($school && $school->isPreUniversity())
        <!-- Affichage pour les écoles pré-universitaires -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-dashboard.card 
                title="📚 MODULE A3 — Structure Académique (Pré-universitaire)"
                subtitle="Gestion complète de la structure éducative"
            >
                <x-slot name="actions">
                    <a href="{{ route('academic.levels') }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                        </svg>
                        Gestion Complète
                    </a>
                </x-slot>

                <div class="p-4 bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-lg">
                    <h4 class="font-semibold text-blue-700 dark:text-blue-300 mb-3">🏫 Gestion Pré-universitaire</h4>
                    <div class="space-y-2">
                        <a href="{{ route('academic.levels') }}" 
                           class="block text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            📊 Niveaux/Cycles
                        </a>
                        <a href="{{ route('academic.classes') }}" 
                           class="block text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            🏛️ Classes
                        </a>
                        <a href="{{ route('academic.subjects') }}" 
                           class="block text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300">
                            📖 Matières
                        </a>
                        <a href="{{ route('admin.students.index') }}" 
                           class="block text-sm font-medium text-blue-700 dark:text-blue-300 hover:text-blue-900 dark:hover:text-blue-200">
                            👥 Étudiants
                        </a>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">État du Module A3:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            ✅ 100% Complet
                        </span>
                    </div>
                </div>
            </x-dashboard.card>

            <!-- Actions rapides Structure Académique Pré-universitaire -->
            <x-dashboard.card 
                title="⚡ Actions Rapides - Structure Pré-universitaire"
                subtitle="Raccourcis pour la gestion académique"
            >
                <div class="grid grid-cols-1 gap-3">
                    <!-- Créations rapides -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h5 class="font-medium text-gray-900 dark:text-white mb-2">➕ Créations Rapides</h5>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.students.create') }}" 
                               class="text-sm px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center">
                                👤 Nouvel Étudiant
                            </a>
                            <a href="{{ route('academic.classes.create') }}" 
                               class="text-sm px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-center">
                                🏛️ Nouvelle Classe
                            </a>
                            <a href="{{ route('academic.subjects.create') }}" 
                               class="text-sm px-3 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-center">
                                📖 Nouvelle Matière
                            </a>
                            <a href="{{ route('academic.levels.create') }}" 
                               class="text-sm px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-center">
                                📊 Nouveau Niveau
                            </a>
                        </div>
                    </div>

                    <!-- Gestion et Rapports -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h5 class="font-medium text-gray-900 dark:text-white mb-2">📊 Gestion & Rapports</h5>
                        <div class="space-y-2">
                            <a href="{{ route('enrollments.index') }}" 
                               class="flex items-center text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                                </svg>
                                Inscriptions
                            </a>
                            <a href="{{ route('admin.teachers.index') }}" 
                               class="flex items-center text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                                </svg>
                                Enseignants
                            </a>
                            <a href="{{ route('admin.assignments.index') }}" 
                               class="flex items-center text-sm text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Affectations Péda.
                            </a>
                        </div>
                    </div>
                </div>
            </x-dashboard.card>
        </div>
        @elseif($school && $school->isUniversity())
        <!-- Affichage pour les écoles universitaires -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-dashboard.card 
                title="🎓 MODULE A3 — Structure Académique (Universitaire)"
                subtitle="Gestion complète de la structure universitaire"
            >
                <x-slot name="actions">
                    <a href="{{ route('university.dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h4a1 1 0 011 1v5m-6 0V9a1 1 0 011-1h4a1 1 0 011 1v2"/>
                        </svg>
                        Gestion Universitaire
                    </a>
                </x-slot>

                <div class="p-4 bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-lg">
                    <h4 class="font-semibold text-purple-700 dark:text-purple-300 mb-3">🎓 Gestion Universitaire</h4>
                    <div class="space-y-2">
                        <a href="{{ route('university.ufrs.index') }}" 
                           class="block text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300">
                            🏛️ UFR (Unités de Formation)
                        </a>
                        <a href="{{ route('university.departments.index') }}" 
                           class="block text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300">
                            🏢 Départements
                        </a>
                        <a href="{{ route('university.programs.index') }}" 
                           class="block text-sm text-purple-600 dark:text-purple-400 hover:text-purple-800 dark:hover:text-purple-300">
                            📚 Programmes d'Études
                        </a>
                        <a href="{{ route('university.dashboard') }}" 
                           class="block text-sm font-medium text-purple-700 dark:text-purple-300 hover:text-purple-900 dark:hover:text-purple-200">
                            🎯 Dashboard Universitaire
                        </a>
                    </div>
                    
                    <div class="mt-3 pt-2 border-t border-purple-200 dark:border-purple-700">
                        <p class="text-xs text-purple-600 dark:text-purple-400">
                            ℹ️ Les fonctionnalités pré-universitaires sont masquées pour cette école universitaire.
                        </p>
                    </div>
                </div>
                
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Système Universitaire:</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400">
                            ✅ 100% Opérationnel
                        </span>
                    </div>
                </div>
            </x-dashboard.card>

            <!-- Actions rapides Structure Universitaire -->
            <x-dashboard.card 
                title="⚡ Actions Rapides - Système Universitaire"
                subtitle="Raccourcis pour la gestion universitaire"
            >
                <div class="grid grid-cols-1 gap-3">
                    <!-- Créations rapides universitaires -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h5 class="font-medium text-gray-900 dark:text-white mb-2">➕ Créations Rapides</h5>
                        <div class="grid grid-cols-2 gap-2">
                            <a href="{{ route('university.ufrs.create') }}" 
                               class="text-sm px-3 py-2 bg-purple-600 text-white rounded hover:bg-purple-700 text-center">
                                🏛️ Nouvelle UFR
                            </a>
                            <a href="{{ route('university.departments.create') }}" 
                               class="text-sm px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-center">
                                🏢 Nouveau Département
                            </a>
                            <a href="{{ route('university.programs.create') }}" 
                               class="text-sm px-3 py-2 bg-green-600 text-white rounded hover:bg-green-700 text-center">
                                📚 Nouveau Programme
                            </a>
                        </div>
                    </div>

                    <!-- Gestion universitaire -->
                    <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                        <h5 class="font-medium text-gray-900 dark:text-white mb-2">📊 Gestion Universitaire</h5>
                        <div class="space-y-2">
                            <a href="{{ route('university.dashboard') }}" 
                               class="flex items-center text-sm text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                Tableau de Bord
                            </a>
                            <a href="{{ route('admin.academic-years.index') }}" 
                               class="flex items-center text-sm text-gray-700 dark:text-gray-300 hover:text-purple-600 dark:hover:text-purple-400">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                Années Académiques
                            </a>
                        </div>
                    </div>
                </div>
            </x-dashboard.card>
        </div>
        @endif

        <!-- MODULE A6 - Supervision & Audits -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                <x-dashboard.card 
                    title="📊 Module A6 — Supervision & Audits"
                    subtitle="Monitoring et activités système"
                >
                    <x-slot name="actions">
                        <a href="{{ route('admin.supervision.index') }}" 
                           class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            Voir Détails
                        </a>
                    </x-slot>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $supervisionStats['today_logins'] }}</p>
                            <p class="text-xs text-blue-600 dark:text-blue-400">Connexions aujourd'hui</p>
                        </div>
                        <div class="text-center p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $supervisionStats['week_unique_users'] }}</p>
                            <p class="text-xs text-green-600 dark:text-green-400">Utilisateurs actifs (7j)</p>
                        </div>
                        <div class="text-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $supervisionStats['active_teachers'] }}</p>
                            <p class="text-xs text-purple-600 dark:text-purple-400">Enseignants actifs</p>
                        </div>
                        <div class="text-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-lg">
                            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400">{{ $supervisionStats['active_students'] }}</p>
                            <p class="text-xs text-orange-600 dark:text-orange-400">Étudiants actifs</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <a href="{{ route('admin.supervision.teacher-activities') }}" 
                           class="block p-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-colors duration-200">
                            <h4 class="font-semibold">🧑‍🏫 Enseignants</h4>
                            <p class="text-sm opacity-90">Activités pédagogiques</p>
                        </a>
                        <a href="{{ route('admin.supervision.student-activities') }}" 
                           class="block p-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-colors duration-200">
                            <h4 class="font-semibold">🎓 Étudiants</h4>
                            <p class="text-sm opacity-90">Engagement académique</p>
                        </a>
                        <a href="{{ route('admin.supervision.user-logs') }}" 
                           class="block p-3 bg-gradient-to-r from-indigo-500 to-indigo-600 text-white rounded-lg hover:from-indigo-600 hover:to-indigo-700 transition-colors duration-200">
                            <h4 class="font-semibold">🔒 Logs</h4>
                            <p class="text-sm opacity-90">Connexions & Audits</p>
                        </a>
                    </div>
                </x-dashboard.card>
            </div>

            <x-dashboard.card 
                title="🔄 Activités Récentes"
                subtitle="Actions système en temps réel"
            >
                <div class="space-y-3 max-h-64 overflow-y-auto">
                    @forelse($recentSystemActivities as $activity)
                    <div class="flex items-start space-x-3">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full mt-2 flex-shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-gray-900 dark:text-white">
                                <span class="font-medium">{{ $activity->user->name }}</span>
                                <span class="text-gray-600 dark:text-gray-400">
                                    a {{ $activity->action }} {{ $activity->entity }}
                                    @if($activity->entity_id) #{{ $activity->entity_id }} @endif
                                </span>
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $activity->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Aucune activité récente</p>
                    </div>
                    @endforelse
                </div>
                
                @if($recentSystemActivities->count() > 0)
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('admin.supervision.index') }}" 
                       class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                        Voir toutes les activités →
                    </a>
                </div>
                @endif
            </x-dashboard.card>
        </div>
    </div>
@endsection