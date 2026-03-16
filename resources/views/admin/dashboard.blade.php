@extends('layouts.app')

@section('title', 'Dashboard Admin - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-900">Dashboard Admin</h1>
    <p class="text-gray-600 mt-2">Bienvenue dans le tableau de bord d'administration</p>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Utilisateurs Total</h3>
        <p class="text-4xl font-bold text-blue-600">{{ $totalUtilisateurs }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Citoyens</h3>
        <p class="text-4xl font-bold text-green-600">{{ $totalCitoyens }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Agents</h3>
        <p class="text-4xl font-bold text-purple-600">{{ $totalAgents }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Total Demandes</h3>
        <p class="text-4xl font-bold text-orange-600">{{ $totalDemandes }}</p>
    </div>
</div>

<!-- Résumé des demandes -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Demandes Pendantes</h3>
        <p class="text-3xl font-bold text-yellow-600">{{ $demandesPendantes }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">En Cours</h3>
        <p class="text-3xl font-bold text-blue-600">{{ $demandesEnCours }}</p>
    </div>
</div>

<!-- Dernières demandes -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Dernières demandes</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Citoyen</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dernieresDemandes as $demande)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900">{{ $demande->titre }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $demande->citoyen->name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full 
                                @if($demande->statut === 'pendante') bg-yellow-100 text-yellow-800
                                @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                                @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                                @elseif($demande->statut === 'rejetee') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($demande->statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-700">Aucune demande</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
