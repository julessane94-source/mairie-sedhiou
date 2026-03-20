@extends('layouts.app')

@section('title', 'Gestion des Agents')

@section('content')
<div class="mb-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold text-gray-900">👥 Gestion des Agents</h1>
        <a href="{{ route('admin.agents.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
            + Ajouter un agent
        </a>
    </div>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="{{ route('admin.agents.index') }}" method="GET" class="flex gap-4">
        <input type="text" name="search" placeholder="Rechercher par nom ou email" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" value="{{ request('search') }}">
        <select name="statut" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Tous les statuts</option>
            <option value="actif" {{ request('statut') === 'actif' ? 'selected' : '' }}>Actif</option>
            <option value="inactif" {{ request('statut') === 'inactif' ? 'selected' : '' }}>Inactif</option>
            <option value="congé" {{ request('statut') === 'congé' ? 'selected' : '' }}>En congé</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition">
            Filtrer
        </button>
    </form>
</div>

<!-- Liste des agents -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Agent</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Téléphone</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Demandes</th>
                    <th class="px-6 py-4 text-center text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($agents as $agent)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4">
                        <div class="text-gray-900 font-semibold">{{ $agent->nom }}</div>
                        <div class="text-sm text-gray-500">{{ $agent->email }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $agent->email }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $agent->telephone ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                            {{ $agent->demandes_count }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <span class="inline-block px-3 py-1 text-sm rounded-full
                            @if($agent->statut === 'actif') bg-green-100 text-green-800
                            @elseif($agent->statut === 'congé') bg-yellow-100 text-yellow-800
                            @else bg-red-100 text-red-800
                            @endif">
                            {{ ucfirst($agent->statut) }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex gap-2">
                            <a href="{{ route('admin.agents.show', $agent) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Voir</a>
                            <a href="{{ route('admin.agents.edit', $agent) }}" class="text-green-600 hover:text-green-800 text-sm font-semibold">Éditer</a>
                            <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">Supprimer</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">Aucun agent trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($agents->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $agents->links() }}
    </div>
    @endif
</div>
@endsection
