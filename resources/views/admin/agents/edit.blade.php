@extends('layouts.app')

@section('title', 'Éditer ' . $agent->nom)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.agents.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">✏️ Éditer l'agent</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.agents.update', $agent) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nom *</label>
                    <input type="text" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required value="{{ $agent->nom }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Prénom *</label>
                    <input type="text" name="prenom" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required value="{{ $agent->prenom ?? '' }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required value="{{ $agent->email }}">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                    <input type="text" name="telephone" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ $agent->telephone ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Spécialité</label>
                    <input type="text" name="specialite" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ $agent->specialite ?? '' }}">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                <input type="text" name="adresse" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ $agent->adresse ?? '' }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Date d'embauche</label>
                <input type="date" name="date_embauche" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ $agent->date_embauche ? $agent->date_embauche->format('Y-m-d') : '' }}">
            </div>

            <!-- Statut -->
            <div class="pt-4 border-t border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Module de statut</h3>
                <form action="{{ route('admin.agents.changerStatut', $agent) }}" method="POST" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="statut" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="actif" {{ $agent->statut === 'actif' ? 'selected' : '' }}>Actif</option>
                        <option value="inactif" {{ $agent->statut === 'inactif' ? 'selected' : '' }}>Inactif</option>
                        <option value="congé" {{ $agent->statut === 'congé' ? 'selected' : '' }}>En congé</option>
                        <option value="suspendu" {{ $agent->statut === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Appliquer</button>
                </form>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    Mettre à jour
                </button>
            </div>
        </form>

        <!-- Danger Zone -->
        <div class="mt-6 pt-6 border-t border-red-200 bg-red-50 p-4 rounded-lg">
            <h3 class="text-red-900 font-semibold mb-2">⚠️ Danger</h3>
            <form action="{{ route('admin.agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Supprimer cet agent ?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Supprimer cet agent</button>
            </form>
        </div>
    </div>
</div>
@endsection
