@extends('layouts.app')

@section('title', 'Détail utilisateur - Admin - Mairi')

@section('content')
<div class="mb-8">
    <a href="{{ route('admin.utilisateurs.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        ← Retour aux utilisateurs
    </a>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <h1 class="text-3xl font-bold text-gray-900">{{ $user->name }}</h1>
    <p class="text-gray-600 mt-2">{{ $user->email }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Informations du compte</h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Nom</p>
                    <p class="text-gray-900 font-medium">{{ $user->name }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Email</p>
                    <p class="text-gray-900 font-medium">{{ $user->email }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-500">Membre depuis le :</p>
                    <p class="text-gray-900 font-medium">
                        {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'Date non disponible' }}
                    </p>
                </div>
            </div>
        </div>

        @if($user->profil)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Informations personnelles</h2>
            
            <div class="grid grid-cols-2 gap-6">
                @if($user->profil->telephone)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Téléphone</p>
                    <p class="text-gray-900">{{ $user->profil->telephone }}</p>
                </div>
                @endif
                
                @if($user->profil->adresse)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Adresse</p>
                    <p class="text-gray-900">{{ $user->profil->adresse }}</p>
                </div>
                @endif
                
                @if($user->profil->ville)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Ville</p>
                    <p class="text-gray-900">{{ $user->profil->ville }}</p>
                </div>
                @endif
                
                @if($user->profil->code_postal)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Code postal</p>
                    <p class="text-gray-900">{{ $user->profil->code_postal }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-8">
            <h2 class="text-lg font-bold text-gray-900 mb-6">Paramètres</h2>
            
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Rôle</label>
                <form action="{{ route('admin.utilisateurs.changerRole', $user) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-2 @error('role') border-red-500 @enderror">
                        <option value="admin" @selected($user->role === 'admin')>Admin</option>
                        <option value="citoyen" @selected($user->role === 'citoyen')>Citoyen</option>
                        <option value="agent" @selected($user->role === 'agent')>Agent</option>
                    </select>
                    
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded">
                        Mettre à jour le rôle
                    </button>
                </form>
            </div>

            <div class="mb-6 pt-6 border-t border-gray-200">
                <label class="block text-gray-700 font-semibold mb-2">Statut</label>
                <form action="{{ route('admin.utilisateurs.changerStatut', $user) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    
                    <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded-lg mb-2 @error('statut') border-red-500 @enderror">
                        <option value="actif" @selected($user->statut === 'actif')>Actif</option>
                        <option value="inactif" @selected($user->statut === 'inactif')>Inactif</option>
                        <option value="suspendu" @selected($user->statut === 'suspendu')>Suspendu</option>
                    </select>
                    
                    <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 rounded">
                        Mettre à jour le statut
                    </button>
                </form>
            </div>

            <div class="pt-6 border-t border-gray-200">
                <h3 class="font-bold text-gray-900 mb-4">Statistiques</h3>
                <div class="space-y-3">
                    @if($user->role === 'citoyen')
                    <div>
                        <p class="text-gray-600 text-sm">Demandes</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $user->demandes->count() }}</p>
                    </div>
                    @elseif($user->role === 'agent')
                    <div>
                        <p class="text-gray-600 text-sm">Demandes assignées</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $user->demandesAssignees->count() }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection