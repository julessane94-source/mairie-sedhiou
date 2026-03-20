@extends('layouts.app')

@section('title', 'Demandes - Agent - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Gestion des demandes</h1>
    <p class="text-gray-600 mt-2">Traiter et assigner les demandes</p>
</div>

<!-- Tabs -->
<div class="mb-6 flex space-x-4 border-b border-gray-200">
    <a href="#mes-demandes" class="px-4 py-2 font-semibold text-blue-600 border-b-2 border-blue-600">Mes demandes assignées</a>
    <a href="#demandes-disponibles" class="px-4 py-2 font-semibold text-gray-600">Demandes pendantes</a>
</div>

<!-- Mes demandes assignées -->
<div id="mes-demandes" class="bg-white rounded-lg shadow overflow-hidden mb-8">
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
                                {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
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

<!-- Demandes pendantes -->
<div id="demandes-disponibles" class="bg-white rounded-lg shadow overflow-hidden">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-900">Demandes en attente d'assignment</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Citoyen</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Priorité</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandesPendantes as $demande)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $demande->titre }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $demande->citoyen->name }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $demande->type }}</td>
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
                            <a href="{{ route('agent.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir & Assigner</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-700">Aucune demande à assigner</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($demandesPendantes->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $demandesPendantes->links() }}
        </div>
    @endif
</div>
@endsection
