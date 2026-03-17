@extends('layouts.app')

@section('title', 'Créer un utilisateur')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.utilisateurs.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">➕ Créer un nouvel utilisateur</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.utilisateurs.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom complet *</label>
                    <input type="text" name="name" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror" 
                        required value="{{ old('name') }}">
                    @error('name')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                    <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror" 
                        required value="{{ old('email') }}">
                    @error('email')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Rôle *</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('role') border-red-500 @enderror" required>
                        <option value="">-- Sélectionner un rôle --</option>
                        <option value="admin" @selected(old('role') === 'admin')>Administrateur</option>
                        <option value="agent" @selected(old('role') === 'agent')>Agent</option>
                        <option value="citoyen" @selected(old('role') === 'citoyen')>Citoyen</option>
                    </select>
                    @error('role')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Statut *</label>
                    <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('statut') border-red-500 @enderror" required>
                        <option value="">-- Sélectionner un statut --</option>
                        <option value="actif" @selected(old('statut') === 'actif')>Actif</option>
                        <option value="inactif" @selected(old('statut') === 'inactif')>Inactif</option>
                        <option value="suspendu" @selected(old('statut') === 'suspendu')>Suspendu</option>
                    </select>
                    @error('statut')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Mot de passe *</label>
                    <input type="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror" 
                        required>
                    @error('password')<span class="text-red-600 text-sm">{{ $message }}</span>@enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmation mot de passe *</label>
                    <input type="password" name="password_confirmation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                    Créer l'utilisateur
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
