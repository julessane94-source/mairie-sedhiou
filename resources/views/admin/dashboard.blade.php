@extends('layouts.app')

@section('title', 'Dashboard Admin - Mairi')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-4xl font-bold text-gray-900">📊 Tableau de bord Administrateur</h1>
        <span class="text-gray-600 text-sm">{{ now()->format('d/m/Y H:i') }}</span>
    </div>
    <p class="text-gray-600">Gestion complète de la plateforme MAIRI</p>
</div>

<!-- DIAGNOSTICS -->
@if(count($diagnostics) > 0)
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-3">⚠️ Diagnostics et alertes</h3>
    @foreach($diagnostics as $diagnostic)
    <div class="mb-3 border-l-4 p-4 rounded-r
        @if($diagnostic['type'] === 'error') border-red-500 bg-red-50
        @elseif($diagnostic['type'] === 'warning') border-yellow-500 bg-yellow-50
        @else border-blue-500 bg-blue-50
        @endif">
        <div class="flex justify-between">
            <div>
                <h4 class="font-semibold text-gray-900">{{ $diagnostic['titre'] }}</h4>
                <p class="text-sm text-gray-700 mt-1">{{ $diagnostic['message'] }}</p>
            </div>
            <span class="text-xs text-blue-600 font-semibold whitespace-nowrap ml-2">{{ $diagnostic['action'] }}</span>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- STATISTIQUES GLOBALES -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold opacity-90 mb-2">Utilisateurs</h3>
        <p class="text-4xl font-bold mb-2">{{ $stats['utilisateurs']['total'] }}</p>
        <div class="text-sm opacity-75">
            👤 Citoyens: {{ $stats['utilisateurs']['citoyens'] }}<br>
            👥 Agents: {{ $stats['utilisateurs']['agents'] }}<br>
            🔑 Admins: {{ $stats['utilisateurs']['admins'] }}
        </div>
    </div>

    <div class="bg-gradient-to-br from-green-500 to-green-600 text-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold opacity-90 mb-2">Demandes</h3>
        <p class="text-4xl font-bold mb-2">{{ $stats['demandes']['total'] }}</p>
        <div class="text-sm opacity-75">
            📌 En cours: {{ $stats['demandes']['en_cours'] }}<br>
            ⏳ Pendantes: {{ $stats['demandes']['pendantes'] }}<br>
            ✅ Acceptées: {{ $stats['demandes']['acceptees'] }}
        </div>
    </div>

    <div class="bg-gradient-to-br from-cyan-500 to-cyan-600 text-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold opacity-90 mb-2">Paiements</h3>
        <p class="text-4xl font-bold mb-2">{{ number_format($stats['paiements']['total']) }}</p>
        <div class="text-sm opacity-75">
            💳 En attente: {{ $stats['paiements']['pending'] }}<br>
            ✅ Payés: {{ $stats['paiements']['paid'] }}<br>
            ❌ Annulés: {{ $stats['paiements']['cancelled'] }}
        </div>
    </div>

    <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-lg shadow p-6">
        <h3 class="text-sm font-semibold opacity-90 mb-2">Présence</h3>
        <p class="text-4xl font-bold mb-2">{{ $agentsPresents }}/{{ $agentsPresents + $agentsAbsents }}</p>
        <div class="text-sm opacity-75">
            ✅ Présents: {{ $agentsPresents }}<br>
            ❌ Absents: {{ $agentsAbsents }}<br>
            📊 Aujourd'hui
        </div>
    </div>
</div>

<!-- PERFORMANCE & INDICATEURS -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Performance</h3>
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Délai moyen:</span>
                <span class="font-bold text-blue-600">{{ $delaiMoyenTraitement }} jours</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Taux satisfaction:</span>
                <span class="font-bold text-green-600">{{ $tauxSatisfaction }}%</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600">Messages non lus:</span>
                <span class="font-bold text-red-600">{{ $stats['messages']['non_lus'] }}</span>
            </div>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">👥 Top Agents performants</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-gray-700 font-semibold">Agent</th>
                        <th class="px-4 py-2 text-center text-gray-700 font-semibold">Demandes</th>
                        <th class="px-4 py-2 text-center text-gray-700 font-semibold">Réussite</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agentStats->sortByDesc('taux_reussite')->take(5) as $stat)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-4 py-2 text-gray-900">
                            <a href="{{ route('admin.agents.show', $stat['agent']) }}" class="text-blue-600 hover:underline">
                                {{ $stat['agent']->nom }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-center text-gray-700">{{ $stat['demandes_total'] }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="inline-block px-2 py-1 bg-green-100 text-green-800 rounded text-xs font-semibold">
                                {{ $stat['taux_reussite'] }}%
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DONNÉES RÉCENTES -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h4 class="font-semibold text-gray-900">📝 Derniers citoyens</h4>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($derniersCitoyens as $citoyen)
            <a href="{{ route('admin.utilisateurs.show', $citoyen) }}" class="block p-4 hover:bg-gray-50 transition">
                <p class="text-gray-900 font-semibold">{{ $citoyen->nom }}</p>
                <p class="text-gray-500 text-sm">{{ $citoyen->email }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ $citoyen->created_at->diffForHumans() }}</p>
            </a>
            @empty
            <div class="p-4 text-center text-gray-500">Aucun citoyen</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h4 class="font-semibold text-gray-900">💳 Derniers paiements</h4>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($derniersPaiements as $payment)
            <div class="p-4 hover:bg-gray-50 transition">
                <div class="flex justify-between items-start mb-1">
                    <p class="text-gray-900 font-semibold text-sm">{{ Str::limit($payment->reference_recu, 15) }}</p>
                    <span class="text-xs px-2 py-1 rounded-full 
                        @if($payment->statut === 'paid') bg-green-100 text-green-800
                        @elseif($payment->statut === 'pending') bg-yellow-100 text-yellow-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($payment->statut) }}
                    </span>
                </div>
                <p class="text-gray-600 text-sm">{{ number_format($payment->montant) }} {{ $payment->devise }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ $payment->created_at->diffForHumans() }}</p>
            </div>
            @empty
            <div class="p-4 text-center text-gray-500">Aucun paiement</div>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h4 class="font-semibold text-gray-900">📋 Dernières demandes</h4>
        </div>
        <div class="divide-y divide-gray-200">
            @forelse($dernieresDemandes as $demande)
            <a href="{{ route('admin.demandes.show', $demande) }}" class="block p-4 hover:bg-gray-50 transition">
                <div class="flex justify-between items-start mb-1">
                    <p class="text-gray-900 font-semibold text-sm">{{ Str::limit($demande->titre, 20) }}</p>
                    <span class="text-xs px-2 py-1 rounded-full
                        @if($demande->statut === 'pendante') bg-yellow-100 text-yellow-800
                        @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                        @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                        @else bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst($demande->statut) }}
                    </span>
                </div>
                <p class="text-gray-600 text-xs">Par: {{ $demande->citoyen->nom }}</p>
                <p class="text-gray-400 text-xs mt-1">{{ $demande->created_at->diffForHumans() }}</p>
            </a>
            @empty
            <div class="p-4 text-center text-gray-500">Aucune demande</div>
            @endforelse
        </div>
    </div>
</div>

<!-- ACTIONS RAPIDES -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">⚡ Actions rapides</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <a href="{{ route('admin.agents.index') }}" class="text-center p-4 border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition">
            <div class="text-2xl mb-2">👥</div>
            <div class="text-sm font-semibold text-gray-900">Gérer agents</div>
        </a>
        <a href="{{ route('admin.pointage.index') }}" class="text-center p-4 border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition">
            <div class="text-2xl mb-2">⏰</div>
            <div class="text-sm font-semibold text-gray-900">Pointage</div>
        </a>
        <a href="{{ route('admin.settings.index') }}" class="text-center p-4 border border-gray-200 rounded-lg hover:bg-purple-50 hover:border-purple-300 transition">
            <div class="text-2xl mb-2">⚙️</div>
            <div class="text-sm font-semibold text-gray-900">Paramètres</div>
        </a>
        <a href="{{ route('admin.demandes.index') }}" class="text-center p-4 border border-gray-200 rounded-lg hover:bg-orange-50 hover:border-orange-300 transition">
            <div class="text-2xl mb-2">📋</div>
            <div class="text-sm font-semibold text-gray-900">Demandes</div>
        </a>
    </div>
</div>
@endsection
