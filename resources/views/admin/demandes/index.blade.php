@extends('layouts.app')

@section('title', 'Demandes - Admin - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Gestion des demandes</h1>
    <p class="text-gray-600 mt-2">Supervisez toutes les demandes du système</p>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="{{ route('admin.demandes.index') }}" method="GET" class="flex gap-4 flex-wrap">
        <select name="statut" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Tous les statuts</option>
            <option value="pendante" @selected(request('statut') === 'pendante')>Pendante</option>
            <option value="en_cours" @selected(request('statut') === 'en_cours')>En cours</option>
            <option value="acceptee" @selected(request('statut') === 'acceptee')>Acceptée</option>
            <option value="rejetee" @selected(request('statut') === 'rejetee')>Rejetée</option>
            <option value="terminer" @selected(request('statut') === 'terminer')>Terminée</option>
        </select>
        
        <select name="priorite" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Toutes les priorités</option>
            <option value="basse" @selected(request('priorite') === 'basse')>Basse</option>
            <option value="normale" @selected(request('priorite') === 'normale')>Normale</option>
            <option value="haute" @selected(request('priorite') === 'haute')>Haute</option>
            <option value="urgente" @selected(request('priorite') === 'urgente')>Urgente</option>
        </select>
        
        <input type="text" name="search" placeholder="Rechercher par titre/citoyen..." value="{{ request('search') }}"
            class="px-3 py-2 border border-gray-300 rounded-lg">
        
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
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Citoyen</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Agent</th>
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
                        <td class="px-6 py-4 text-gray-700">{{ $demande->citoyen->name }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            @if($demande->agentAssigne)
                                {{ $demande->agentAssigne->name }}
                            @else
                                <span class="text-gray-400 italic">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full font-semibold
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
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-700">Aucune demande trouvée</td>
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
