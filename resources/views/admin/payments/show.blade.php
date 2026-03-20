@extends('layouts.app')

@section('title', "Détails du paiement")

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.agents.show', request('agent_id')) }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">💳 Détails du paiement</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Informations principales -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📋 Informations de paiement</h2>
                
                <div class="space-y-4">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">ID Paiement</span>
                        <span class="font-semibold text-gray-900">#{{ $payment->id ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Montant</span>
                        <span class="font-semibold text-gray-900">{{ number_format($payment->montant ?? 0, 0, ',', ' ') }} {{ $payment->devise ?? 'XOF' }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Statut</span>
                        <span class="inline-block px-3 py-1 rounded-full font-semibold text-white text-sm
                            {{ $payment->statut === 'completed' ? 'bg-green-500' : '' }}
                            {{ $payment->statut === 'pending' ? 'bg-yellow-500' : '' }}
                            {{ $payment->statut === 'failed' ? 'bg-red-500' : '' }}
                            {{ $payment->statut === 'cancelled' ? 'bg-gray-500' : '' }}
                        ">
                            {{ match($payment->statut ?? 'pending') {
                                'completed' => '✓ Confirmé',
                                'pending' => '⏳ En attente',
                                'failed' => '✗ Échoué',
                                'cancelled' => '⚠ Annulé',
                                default => ucfirst($payment->statut)
                            } }}
                        </span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Méthode</span>
                        <span class="font-semibold text-gray-900">{{ $payment->methode ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Date de creation</span>
                        <span class="font-semibold text-gray-900">{{ $payment->created_at->format('d/m/Y H:i') ?? 'N/A' }}</span>
                    </div>

                    @if($payment->confirmed_at)
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Date de confirmation</span>
                        <span class="font-semibold text-gray-900">{{ $payment->confirmed_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Demande associée -->
            @if($payment->demande)
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📄 Demande associée</h2>

                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-gray-600">Titre</p>
                        <p class="font-semibold text-gray-900">{{ $payment->demande->titre ?? 'N/A' }}</p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-600">Description</p>
                        <p class="text-gray-700">{{ $payment->demande->description ?? 'N/A' }}</p>
                    </div>

                    <div class="flex gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Priorité</p>
                            <span class="inline-block px-3 py-1 rounded-full font-semibold text-white text-xs
                                {{ $payment->demande->priorite === 'haute' ? 'bg-red-500' : '' }}
                                {{ $payment->demande->priorite === 'moyenne' ? 'bg-yellow-500' : '' }}
                                {{ $payment->demande->priorite === 'basse' ? 'bg-green-500' : '' }}
                            ">
                                {{ ucfirst($payment->demande->priorite ?? 'N/A') }}
                            </span>
                        </div>

                        <div>
                            <p class="text-sm text-gray-600">Statut</p>
                            <span class="inline-block px-3 py-1 rounded-full font-semibold text-white text-xs
                                {{ $payment->demande->statut === 'acceptée' ? 'bg-green-500' : '' }}
                                {{ $payment->demande->statut === 'en_cours' ? 'bg-blue-500' : '' }}
                                {{ $payment->demande->statut === 'rejetée' ? 'bg-red-500' : '' }}
                                {{ $payment->demande->statut === 'en_attente' ? 'bg-yellow-500' : '' }}
                            ">
                                {{ ucfirst($payment->demande->statut ?? 'N/A') }}
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('admin.demandes.show', $payment->demande->id) }}" class="inline-block mt-2 text-blue-600 hover:underline">
                        Voir la demande complète →
                    </a>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar actions -->
        <div>
            <div class="bg-white rounded-lg shadow p-6 mb-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">⚙️ Actions</h3>

                <div class="space-y-2">
                    @if($payment->statut === 'pending')
                        <form action="" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold text-sm">
                                ✓ Confirmer
                            </button>
                        </form>

                        <form action="" method="POST" class="block">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold text-sm">
                                ✗ Rejeter
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.agents.show', $payment->agent_id) }}" class="block text-center px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 font-semibold text-sm">
                        👤 Agent
                    </a>

                    @if($payment->demande)
                        <a href="{{ route('admin.demandes.show', $payment->demande->id) }}" class="block text-center px-4 py-2 border border-gray-600 text-gray-600 rounded-lg hover:bg-gray-50 font-semibold text-sm">
                            📄 Demande
                        </a>
                    @endif
                </div>
            </div>

            <!-- Informations supplémentaires -->
            <div class="bg-gray-50 rounded-lg border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-900 mb-3">📊 Détails techniques</h3>
                <dl class="text-sm space-y-2">
                    <div>
                        <dt class="text-gray-600">Transaction ID</dt>
                        <dd class="font-mono text-xs text-gray-900 break-all">{{ $payment->transaction_id ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">Référence API</dt>
                        <dd class="font-mono text-xs text-gray-900">{{ $payment->reference_api ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-600">IP Address</dt>
                        <dd class="font-mono text-xs text-gray-900">{{ $payment->ip_address ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>

    <!-- Historique des modifications -->
    @if(isset($payment->logs) && count($payment->logs) > 0)
    <div class="mt-6 bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">📜 Historique</h2>

        <div class="space-y-3">
            @foreach($payment->logs as $log)
                <div class="flex gap-3 py-3 border-b last:border-b-0">
                    <div class="w-2 h-2 bg-blue-600 rounded-full mt-2 flex-shrink-0"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $log['action'] }}</p>
                        <p class="text-xs text-gray-600">{{ $log['timestamp'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
