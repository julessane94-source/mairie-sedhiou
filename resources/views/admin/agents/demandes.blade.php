@extends('layouts.app')

@section('title', "Détails des demandes de l'agent")

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex justify-between items-start">
        <div>
            <a href="{{ route('admin.agents.show', $agent->id ?? request('agent_id')) }}" class="text-blue-600 hover:underline">← Retour</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">📋 Demandes assignées</h1>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-600">Agent:</p>
            <p class="font-semibold text-gray-900">{{ $agent->name ?? 'N/A' }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Statut</label>
                <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">-- Tous --</option>
                    <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected' : '' }}>En attente</option>
                    <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected' : '' }}>En cours</option>
                    <option value="acceptée" {{ request('statut') === 'acceptée' ? 'selected' : '' }}>Acceptée</option>
                    <option value="rejetée" {{ request('statut') === 'rejetée' ? 'selected' : '' }}>Rejetée</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Priorité</label>
                <select name="priorite" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">-- Tous --</option>
                    <option value="basse" {{ request('priorite') === 'basse' ? 'selected' : '' }}>Basse</option>
                    <option value="moyenne" {{ request('priorite') === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                    <option value="haute" {{ request('priorite') === 'haute' ? 'selected' : '' }}>Haute</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Recherche</label>
                <input type="search" name="search" placeholder="Titre ou description..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm" value="{{ request('search') }}">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold text-sm">🔍 Filtrer</button>
                <a href="" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold text-sm">↻ Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">📊 Total assignées</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">⏳ En attente/cours</p>
            <p class="text-2xl font-bold text-yellow-600">{{ ($stats['en_attente'] ?? 0) + ($stats['en_cours'] ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">✓ Acceptées</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['acceptees'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">✗ Rejetées</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['rejetees'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Tableau des demandes -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">📋 Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">📝 Citoyen</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">🎯 Priorité</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">📊 Statut</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">📅 Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">💳 Paiement</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($demandes ?? [] as $demande)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900">{{ $demande->titre ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-600 mt-1">{{ Str::limit($demande->description, 40) }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $demande->citoyen->name ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-block px-3 py-1 rounded-full font-semibold text-white text-xs
                                {{ $demande->priorite === 'haute' ? 'bg-red-500' : '' }}
                                {{ $demande->priorite === 'moyenne' ? 'bg-yellow-500' : '' }}
                                {{ $demande->priorite === 'basse' ? 'bg-green-500' : '' }}
                            ">
                                {{ ucfirst($demande->priorite ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-block px-3 py-1 rounded-full font-semibold text-white text-xs
                                {{ $demande->statut === 'acceptée' ? 'bg-green-500' : '' }}
                                {{ $demande->statut === 'en_cours' ? 'bg-blue-500' : '' }}
                                {{ $demande->statut === 'rejetée' ? 'bg-red-500' : '' }}
                                {{ $demande->statut === 'en_attente' ? 'bg-yellow-500' : '' }}
                            ">
                                {{ ucfirst($demande->statut ?? 'N/A') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <div class="text-xs">
                                <p>Créée: {{ $demande->created_at->format('d/m/Y') }}</p>
                                @if($demande->updated_at && $demande->updated_at !== $demande->created_at)
                                    <p>Modifiée: {{ $demande->updated_at->format('d/m/Y') }}</p>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($demande->payments()->exists())
                                <div class="space-y-1">
                                    @foreach($demande->payments as $payment)
                                        <div class="text-xs">
                                            <span class="font-semibold">{{ number_format($payment->montant, 0, ',', ' ') }} {{ $payment->devise }}</span>
                                            <span class="inline-block px-2 py-0.5 rounded text-white text-xs font-semibold
                                                {{ $payment->statut === 'completed' ? 'bg-green-500' : '' }}
                                                {{ $payment->statut === 'pending' ? 'bg-yellow-500' : '' }}
                                                {{ $payment->statut === 'failed' ? 'bg-red-500' : '' }}
                                            ">
                                                {{ match($payment->statut) { 'completed' => '✓', 'pending' => '⏳', 'failed' => '✗', default => '?' } }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('admin.demandes.show', $demande->id) }}" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">👁️</a>
                                <a href="{{ route('admin.demandes.edit', $demande->id) }}" class="text-green-600 hover:text-green-800 font-semibold text-sm">✏️</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            Aucune demande assignée à cet agent
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination (si applicable) -->
    @if(isset($demandes) && method_exists($demandes, 'links'))
    <div class="mt-6">
        {{ $demandes->links() }}
    </div>
    @endif
</div>
@endsection
