@extends('layouts.dashboard')

@section('title', 'Gestion Financière')

@section('content')
<!-- Page Header -->
<div class="bg-white dark:bg-gray-800 shadow mb-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Gestion Financière</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tableau de bord des finances et suivi des paiements</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('finance.school-fees.index') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Frais Scolaires
                </a>
                <a href="{{ route('finance.payments.index') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition duration-150 inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 8h6m-6 4h6"/>
                    </svg>
                    Paiements
                </a>
            </div>
        </div>
    </div>
</div>

<div class="space-y-6">
    <div class="mx-auto">
        @if (session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">Frais Totaux</h3>
                            <p class="text-2xl font-bold">{{ number_format($totalFeesAmount, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="text-3xl opacity-80">
                            💰
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gradient-to-br from-green-500 to-green-600 text-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">Paiements Reçus</h3>
                            <p class="text-2xl font-bold">{{ number_format($totalPayments, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="text-3xl opacity-80">
                            ✅
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gradient-to-br from-orange-500 to-orange-600 text-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">Paiements en Attente</h3>
                            <p class="text-2xl font-bold">{{ $pendingPayments }}</p>
                        </div>
                        <div class="text-3xl opacity-80">
                            ⏳
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gradient-to-br from-purple-500 to-purple-600 text-white">
                    <div class="flex items-center">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">Solde Restant</h3>
                            <p class="text-2xl font-bold">{{ number_format($totalFeesAmount - $totalPayments, 0, ',', ' ') }} FCFA</p>
                        </div>
                        <div class="text-3xl opacity-80">
                            📊
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Raccourcis d'actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            <a href="{{ route('finance.school-fees.create') }}" 
               class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 rounded-full mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Nouveau Frais Scolaire</h3>
                        <p class="text-sm text-gray-500">Ajouter un nouveau type de frais</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('finance.payments.create') }}" 
               class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 rounded-full mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Enregistrer Paiement</h3>
                        <p class="text-sm text-gray-500">Nouveau paiement d'étudiant</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('finance.student-balances') }}" 
               class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 rounded-full mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Consulter Soldes</h3>
                        <p class="text-sm text-gray-500">Soldes des étudiants</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Navigation des modules -->
        <div class="bg-white shadow-sm sm:rounded-lg mb-8">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Modules Financiers</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('finance.school-fees.index') }}" 
                       class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="text-2xl mb-2">🏫</div>
                        <div class="text-sm font-medium text-gray-900">Frais Scolaires</div>
                    </a>

                    <a href="{{ route('finance.payments.index') }}" 
                       class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="text-2xl mb-2">💳</div>
                        <div class="text-sm font-medium text-gray-900">Paiements</div>
                    </a>

                    <a href="{{ route('finance.student-balances') }}" 
                       class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="text-2xl mb-2">📊</div>
                        <div class="text-sm font-medium text-gray-900">Soldes Étudiants</div>
                    </a>

                    <a href="{{ route('finance.reports') }}" 
                       class="text-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="text-2xl mb-2">📈</div>
                        <div class="text-sm font-medium text-gray-900">Rapports</div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Derniers paiements -->
        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Derniers Paiements</h3>
                    <a href="{{ route('finance.payments.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Voir tout →
                    </a>
                </div>

                @if($recentPayments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Étudiant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frais</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Méthode</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($recentPayments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $payment->student->user->name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ Str::limit($payment->schoolFee->name, 30) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->formatted_payment_method }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->payment_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payment->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $payment->formatted_status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-8">
                    <div class="text-gray-400 text-4xl mb-4">💳</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun paiement récent</h3>
                    <p class="text-gray-500">Les derniers paiements apparaîtront ici.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection