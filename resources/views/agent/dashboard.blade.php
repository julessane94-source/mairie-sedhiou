@extends('layouts.app')

@section('title', 'Dashboard Agent - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-900">Dashboard Agent</h1>
    <p class="text-gray-600 mt-2">Traitez et gérez les demandes</p>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Demandes Assignées</h3>
        <p class="text-4xl font-bold text-blue-600">{{ $demandesAssignees->total() }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">En Cours</h3>
        <p class="text-4xl font-bold text-yellow-600">{{ $demandesEnCours }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">À Traiter</h3>
        <p class="text-4xl font-bold text-orange-600">{{ $demandesPendantes }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Complétées</h3>
        <p class="text-4xl font-bold text-green-600">{{ $demandesTerminees }}</p>
    </div>
</div>

<!-- Mes demandes -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Demandes qui me sont assignées</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Citoyen</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Priorité</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandesAssignees as $demande)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $demande->titre }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $demande->citoyen->name }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full 
                                @if($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                                @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                                @elseif($demande->statut === 'rejetee') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($demande->statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold 
                                @if($demande->priorite === 'urgente') text-red-600
                                @elseif($demande->priorite === 'haute') text-orange-600
                                @elseif($demande->priorite === 'normale') text-blue-600
                                @else text-gray-600
                                @endif">
                                {{ ucfirst($demande->priorite) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('agent.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-700">Aucune demande assignée</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($demandesAssignees->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $demandesAssignees->links() }}
        </div>
    @endif
</div>
@endsection
