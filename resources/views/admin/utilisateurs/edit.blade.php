@extends('layouts.app')

@section('title', 'Éditer ' . $user->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.utilisateurs.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">✏️ Éditer l'utilisateur</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.utilisateurs.update', $user) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('name') border-red-500 @enderror" 
                        required value="{{ old('name', $user->name) }}">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('email') border-red-500 @enderror" 
                        required value="{{ old('email', $user->email) }}">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @if($user->role === 'citoyen')
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Prénom</label>
                    <input type="text" name="prenom" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        value="{{ old('prenom', $user->prenom ?? '') }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de famille</label>
                    <input type="text" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        value="{{ old('nom', $user->nom ?? '') }}">
                </div>
            </div>

            @if($user->profil)
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations personnelles</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Date de naissance</label>
                        <input type="date" name="date_naissance" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            value="{{ old('date_naissance', $user->profil->date_naissance ? date('Y-m-d', strtotime($user->profil->date_naissance)) : '') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            value="{{ old('lieu_naissance', $user->profil->lieu_naissance ?? '') }}">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                    <input type="text" name="adresse" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        value="{{ old('adresse', $user->profil->adresse ?? '') }}">
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Numéro de registre</label>
                    <input type="text" name="numero_registre" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        value="{{ old('numero_registre', $user->profil->numero_registre ?? '') }}">
                </div>
            </div>
            @endif
            @endif

            @if($user->role === 'agent')
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations de l'agent</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                        <input type="text" name="telephone" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Spécialité</label>
                        <input type="text" name="specialite" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                    <input type="text" name="adresse" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>
            @endif

            <!-- Paramètres d'accès -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Paramètres d'accès</h3>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rôle *</label>
                        <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="admin" @selected($user->role === 'admin')>Administrateur</option>
                            <option value="agent" @selected($user->role === 'agent')>Agent</option>
                            <option value="citoyen" @selected($user->role === 'citoyen')>Citoyen</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Statut *</label>
                        <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="actif" @selected($user->statut === 'actif')>Actif</option>
                            <option value="inactif" @selected($user->statut === 'inactif')>Inactif</option>
                            <option value="suspendu" @selected($user->statut === 'suspendu')>Suspendu</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    Mettre à jour
                </button>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="mt-6 pt-6 border-t border-red-200 bg-red-50 p-4 rounded-lg">
            <h3 class="text-red-900 font-semibold mb-2">⚠️ Zone dangereuse</h3>
            <form action="{{ route('admin.utilisateurs.destroy', $user) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Supprimer cet utilisateur</button>
            </form>
        </div>
    </div>
</div>
@endsection
