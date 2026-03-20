@extends('layouts.app')

@section('title', 'Détails paiement - Mairi')

@section('content')
<div class="mb-8">
    <a href="{{ route('citoyen.payments.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        ← Retour aux paiements
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Détails du paiement</h1>
    <p class="text-gray-600 mt-2">Référence: <code class="bg-gray-100 px-2 py-1 rounded">{{ $payment->reference_recu }}</code></p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <!-- Informations principales -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Informations du paiement</h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Montant</p>
                    <p class="text-3xl font-bold text-blue-600">{{ number_format($payment->montant, 2, ',', ' ') }}</p>
                    <p class="text-gray-700">{{ $payment->devise }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Statut</p>
                    <span class="inline-block px-3 py-1 text-lg rounded-full font-bold
                        @if($payment->statut === 'paid') bg-green-100 text-green-800
                        @elseif($payment->statut === 'pending') bg-yellow-100 text-yellow-800
                        @elseif($payment->statut === 'cancelled') bg-red-100 text-red-800
                        @elseif($payment->statut === 'refunded') bg-blue-100 text-blue-800
                        @endif">
                        {{ $formattedInfo['statut'] }}
                    </span>
                </div>

                <div>
                    <p class="text-gray-600 text-sm font-semibold">Méthode</p>
                    <p class="text-gray-900 font-medium">{{ $formattedInfo['methode'] }}</p>
                </div>

                <div>
                    <p class="text-gray-600 text-sm font-semibold">Créé le</p>
                    <p class="text-gray-900">{{ $formattedInfo['date_creation'] }}</p>
                </div>

                @if($payment->isPaid())
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Payé le</p>
                    <p class="text-gray-900">{{ $formattedInfo['date_paiement'] }}</p>
                </div>
                @endif

                @if($payment->numero_transaction)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">N° Transaction</p>
                    <p class="text-gray-900 font-mono">{{ $payment->numero_transaction }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Demande associée -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Demande associée</h2>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-700"><strong>Titre:</strong> {{ $payment->demande->titre }}</p>
                <p class="text-gray-700"><strong>Type:</strong> {{ $payment->demande->type }}</p>
                <p class="text-gray-700"><strong>Créée le:</strong> {{ $payment->demande->created_at->format('d/m/Y H:i') }}</p>
                <a href="{{ route('citoyen.demandes.show', $payment->demande) }}" class="text-blue-600 hover:underline mt-2 inline-block">
                    Voir la demande →
                </a>
            </div>
        </div>

        @if($payment->description)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Notes</h2>
            <p class="text-gray-700 whitespace-pre-wrap">{{ $payment->description }}</p>
        </div>
        @endif
    </div>

    <!-- Actions latérales -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
            
            <div class="space-y-3">
                @if($payment->isPaid())
                    <!-- Boutons pour paiement complété -->
                    <a href="{{ route('citoyen.payments.receipt.download', $payment) }}" 
                        class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                        📥 Télécharger le reçu
                    </a>
                    
                    <button onclick="window.open('{{ route('citoyen.payments.receipt.preview', $payment) }}', '_blank')"
                        class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                        👁️ Prévisualiser le reçu
                    </button>

                    <button onclick="window.print()" class="block w-full text-center bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transition">
                        🖨️ Imprimer
                    </button>
                @elseif($payment->isPending())
                    <!-- Boutons pour paiement en attente -->
                    <form action="{{ route('citoyen.payments.markAsPaid', $payment) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-gray-700 font-semibold text-sm mb-1">Numéro de transaction (optionnel)</label>
                            <input type="text" name="numero_transaction" placeholder="Ex: TRX2026031600123"
                                class="w-full px-3 py-2 border border-gray-300 rounded text-sm">
                        </div>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition text-sm">
                            ✓ Marquer comme payé
                        </button>
                    </form>

                    <form action="{{ route('citoyen.payments.cancel', $payment) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr?');">
                        @csrf
                        <textarea name="raison" placeholder="Motif d'annulation..." rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded text-sm mb-2"></textarea>
                        <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition text-sm">
                            ✗ Annuler le paiement
                        </button>
                    </form>
                @endif
            </div>

            <!-- Informations complémentaires -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <h3 class="font-bold text-gray-900 mb-4 text-sm">Aide</h3>
                <div class="text-xs text-gray-600 space-y-2">
                    <p>
                        <strong>📋 Référence:</strong><br>
                        <code class="bg-gray-100 px-1 rounded break-all">{{ $payment->reference_recu }}</code>
                    </p>
                    
                    @if($payment->isPaid())
                    <p class="text-green-700 bg-green-50 p-2 rounded">
                        ✓ Votre paiement a été complété. Vous pouvez télécharger votre reçu.
                    </p>
                    @else
                    <p class="text-yellow-700 bg-yellow-50 p-2 rounded">
                        ⏳ Paiement en attente de confirmation. Cliquez sur "Marquer comme payé" une fois le versement effectué.
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
