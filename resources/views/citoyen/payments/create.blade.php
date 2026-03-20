@extends('layouts.app')

@section('title', 'Créer un paiement - Mairi')

@section('content')
<div class="mb-8">
    <a href="{{ route('citoyen.payments.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        ← Retour aux paiements
    </a>
    <h1 class="text-3xl font-bold text-gray-900">Créer un paiement</h1>
    <p class="text-gray-600 mt-2">Paiement pour: <strong>{{ $demande->titre }}</strong></p>
</div>

@if($existingPayment)
    <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded mb-6">
        <p class="text-blue-800">
            <strong>Note:</strong> Un paiement existe déjà pour cette demande (Référence: {{ $existingPayment->reference_recu }})
        </p>
    </div>
@endif

<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <!-- Informations de la demande -->
        <div class="mb-8 pb-8 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Demande</h3>
            <div class="bg-gray-50 p-4 rounded">
                <p class="text-gray-700"><strong>Titre:</strong> {{ $demande->titre }}</p>
                <p class="text-gray-700"><strong>Type:</strong> {{ $demande->type }}</p>
                <p class="text-gray-700"><strong>Créée le:</strong> {{ $demande->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <form action="{{ route('citoyen.payments.store', $demande) }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Montant à payer</label>
                <div class="flex gap-2">
                    <input type="number" name="montant" step="0.01" min="0.01" value="{{ old('montant') }}" required
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                        @error('montant') border-red-500 @enderror"
                        placeholder="0.00">
                    <select class="px-4 py-2 border border-gray-300 rounded-lg bg-white">
                        <option>XOF</option>
                        <option>EUR</option>
                        <option>USD</option>
                    </select>
                </div>
                @error('montant')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Méthode de paiement</label>
                <select name="methode_paiement" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('methode_paiement') border-red-500 @enderror">
                    <option value="">-- Sélectionnez une méthode --</option>
                    <option value="virement" @selected(old('methode_paiement') === 'virement')>Virement bancaire</option>
                    <option value="cheque" @selected(old('methode_paiement') === 'cheque')>Chèque</option>
                    <option value="especes" @selected(old('methode_paiement') === 'especes')>Espèces</option>
                    <option value="carte" @selected(old('methode_paiement') === 'carte')>Carte bancaire</option>
                    <option value="mobile_money" @selected(old('methode_paiement') === 'mobile_money')>Paiement mobile</option>
                </select>
                @error('methode_paiement')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Description (optionnel)</label>
                <textarea name="description" rows="4" placeholder="Notes additionnelles..."
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('description') }}</textarea>
            </div>

            <div class="bg-blue-50 border border-blue-200 p-4 rounded mb-6">
                <p class="text-blue-800 text-sm">
                    <strong>ℹ️ Info:</strong> Après création, vous recevrez une référence unique. Après paiement, vous pourrez télécharger votre reçu.
                </p>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Créer le paiement
                </button>
                <a href="{{ route('citoyen.payments.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-2 px-6 rounded">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
