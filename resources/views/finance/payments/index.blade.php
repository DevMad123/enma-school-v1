@extends('layouts.app')

@section('title', 'Paiements - Enma School')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestion des Paiements') }}
        </h2>
        <a href="{{ route('finance.payments.create') }}" 
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            + Nouveau Paiement
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

        <!-- Navigation de retour -->
        <div class="mb-6">
            <a href="{{ route('finance.index') }}" 
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                ← Retour au tableau de bord financier
            </a>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                @if($payments->count() > 0)
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
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reçu</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($payments as $payment)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                <span class="text-sm font-medium text-gray-700">
                                                    {{ strtoupper(substr($payment->student->user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $payment->student->user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $payment->student->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $payment->schoolFee->name }}</div>
                                    <div class="text-sm text-gray-500">{{ number_format($payment->schoolFee->amount, 0, ',', ' ') }} FCFA total</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($payment->amount, 0, ',', ' ') }} FCFA
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        {{ $payment->formatted_payment_method }}
                                    </span>
                                    @if($payment->transaction_reference)
                                        <div class="text-xs text-gray-500 mt-1">
                                            Réf: {{ $payment->transaction_reference }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $payment->payment_date->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($payment->status === 'confirmed') bg-green-100 text-green-800
                                        @elseif($payment->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($payment->status === 'cancelled') bg-red-100 text-red-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $payment->formatted_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($payment->receipt)
                                        <a href="{{ route('finance.receipts.download', $payment->receipt) }}" 
                                           class="text-blue-600 hover:text-blue-900 font-medium">
                                            📄 {{ $payment->receipt->receipt_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">Pas de reçu</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        @if($payment->status === 'pending')
                                            <form method="POST" action="{{ route('finance.payments.confirm', $payment) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-green-600 hover:text-green-900"
                                                        onclick="return confirm('Confirmer ce paiement ?')">
                                                    Confirmer
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="{{ route('finance.payments.cancel', $payment) }}" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-900"
                                                        onclick="return confirm('Annuler ce paiement ?')">
                                                    Annuler
                                                </button>
                                            </form>
                                        @endif

                                        @if($payment->receipt)
                                            <a href="{{ route('finance.receipts.download', $payment->receipt) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                Télécharger
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $payments->links() }}
                </div>
                @else
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">💳</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun paiement enregistré</h3>
                    <p class="text-gray-500 mb-6">Les paiements des étudiants apparaîtront ici une fois enregistrés.</p>
                    <a href="{{ route('finance.payments.create') }}" 
                       class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                        Enregistrer le premier paiement
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection