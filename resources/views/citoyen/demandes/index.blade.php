@extends('layouts.app')

@section('title', 'Mes demandes - Mairi')

@section('content')
<div class="mb-8 flex justify-between items-center">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Mes demandes</h1>
        <p class="text-gray-600 mt-2">Consultez l'état de vos demandes</p>
    </div>
    <a href="{{ route('citoyen.demandes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        + Nouvelle demande
    </a>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="{{ route('citoyen.demandes.index') }}" method="GET" class="flex gap-4">
        <select name="statut" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Tous les statuts</option>
            <option value="pendante" @selected(request('statut') === 'pendante')>Pendante</option>
            <option value="en_cours" @selected(request('statut') === 'en_cours')>En cours</option>
            <option value="acceptee" @selected(request('statut') === 'acceptee')>Acceptée</option>
            <option value="rejetee" @selected(request('statut') === 'rejetee')>Rejetée</option>
        </select>
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filtrer</button>
    </form>
</div>

<!-- Liste des demandes -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Priorité</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $demande->titre }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $demande->type }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full 
                                @if($demande->statut === 'pendante') bg-yellow-100 text-yellow-800
                                @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                                @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                                @elseif($demande->statut === 'rejetee') bg-red-100 text-red-800
                                @elseif($demande->statut === 'terminer') bg-purple-100 text-purple-800
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
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('citoyen.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-700">
                            Aucune demande. <a href="{{ route('citoyen.demandes.create') }}" class="text-blue-600 hover:underline">Créer une nouvelle</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($demandes->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $demandes->links() }}
        </div>
    @endif
</div>
@endsection
