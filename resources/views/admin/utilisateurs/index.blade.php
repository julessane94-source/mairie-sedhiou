@extends('layouts.app')

@section('title', 'Utilisateurs - Admin - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Gestion des utilisateurs</h1>
    <p class="text-gray-600 mt-2">Gérez les rôles et statuts des utilisateurs</p>
</div>

<!-- Filtres -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="{{ route('admin.utilisateurs.index') }}" method="GET" class="flex gap-4 flex-wrap">
        <select name="role" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Tous les rôles</option>
            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            <option value="citoyen" @selected(request('role') === 'citoyen')>Citoyen</option>
            <option value="agent" @selected(request('role') === 'agent')>Agent</option>
        </select>
        
        <select name="statut" class="px-3 py-2 border border-gray-300 rounded-lg">
            <option value="">Tous les statuts</option>
            <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
            <option value="inactif" @selected(request('statut') === 'inactif')>Inactif</option>
            <option value="suspendu" @selected(request('statut') === 'suspendu')>Suspendu</option>
        </select>
        
        <input type="text" name="search" placeholder="Rechercher par nom/email..." value="{{ request('search') }}"
            class="px-3 py-2 border border-gray-300 rounded-lg">
        
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Filtrer</button>
    </form>
</div>

<!-- Liste des utilisateurs -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Nom</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Rôle</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Depuis le</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($utilisateurs as $user)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $user->email }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full font-semibold
                                @if($user->role === 'admin') bg-red-100 text-red-800
                                @elseif($user->role === 'agent') bg-green-100 text-green-800
                                @else bg-blue-100 text-blue-800
                                @endif">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full font-semibold
                                @if($user->statut === 'actif') bg-green-100 text-green-800
                                @elseif($user->statut === 'inactif') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($user->statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('admin.utilisateurs.show', $user) }}" class="text-blue-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-700">Aucun utilisateur trouvé</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($utilisateurs->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $utilisateurs->links() }}
        </div>
    @endif
</div>
@endsection
