@extends('layouts.app')

@section('title', 'Paiements - Mairi')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-4xl font-bold text-gray-900">Mes Paiements</h1>
        <p class="text-gray-600 mt-2">Gérez vos paiements et téléchargez vos reçus</p>
    </div>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Total Payé</h3>
        <p class="text-3xl font-bold text-green-600">{{ number_format($statistiques['total_montant'], 2, ',', ' ') }} XOF</p>
    </div>
    
    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">En Attente</h3>
        <p class="text-3xl font-bold text-blue-600">{{ number_format($statistiques['paiements_en_attente'], 2, ',', ' ') }} XOF</p>
    </div>
    
    <div class="bg-purple-50 border-l-4 border-purple-500 p-6 rounded">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Paiements Complétés</h3>
        <p class="text-3xl font-bold text-purple-600">{{ $statistiques['nombre_payes'] }}</p>
    </div>
    
    <div class="bg-yellow-50 border-l-4 border-yellow-500 p-6 rounded">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">En Attente</h3>
        <p class="text-3xl font-bold text-yellow-600">{{ $statistiques['nombre_en_attente'] }}</p>
    </div>
</div>

<!-- Liste des paiements -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Référence</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Demande</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Montant</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Méthode</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900 font-mono text-sm">{{ $payment->reference_recu }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            <a href="{{ route('citoyen.demandes.show', $payment->demande) }}" class="text-blue-600 hover:underline">
                                {{ $payment->demande->titre }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-gray-900 font-bold">
                            {{ number_format($payment->montant, 2, ',', ' ') }} {{ $payment->devise }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full font-semibold
                                @if($payment->statut === 'paid') bg-green-100 text-green-800
                                @elseif($payment->statut === 'pending') bg-yellow-100 text-yellow-800
                                @elseif($payment->statut === 'cancelled') bg-red-100 text-red-800
                                @elseif($payment->statut === 'refunded') bg-blue-100 text-blue-800
                                @endif">
                                @if($payment->statut === 'paid') ✓ Payé
                                @elseif($payment->statut === 'pending') ⏳ En attente
                                @elseif($payment->statut === 'cancelled') ✗ Annulé
                                @elseif($payment->statut === 'refunded') ↩️ Remboursé
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 text-sm capitalize">{{ str_replace('_', ' ', $payment->methode_paiement) }}</td>
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $payment->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 text-sm space-x-2">
                            <a href="{{ route('citoyen.payments.show', $payment) }}" class="text-blue-600 hover:underline">Détails</a>
                            @if($payment->isPaid())
                                <a href="{{ route('citoyen.payments.receipt.download', $payment) }}" class="text-green-600 hover:underline">
                                    📥 Reçu
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-700">
                            Aucun paiement. <a href="{{ route('citoyen.demandes.index') }}" class="text-blue-600 hover:underline">Voir vos demandes</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($payments->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
