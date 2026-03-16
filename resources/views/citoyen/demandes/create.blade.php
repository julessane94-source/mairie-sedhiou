@extends('layouts.app')

@section('title', 'Nouvelle demande - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Créer une nouvelle demande</h1>
    <p class="text-gray-600 mt-2">Remplissez le formulaire pour soumettre votre demande</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('citoyen.demandes.store') }}" method="POST">
            @csrf
            
            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Titre de la demande</label>
                <input type="text" name="titre" value="{{ old('titre') }}" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('titre') border-red-500 @enderror">
                @error('titre')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Type de demande</label>
                <select name="type" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('type') border-red-500 @enderror">
                    <option value="">-- Sélectionnez une option --</option>
                    <option value="Certificat">Certificat</option>
                    <option value="Autorisation">Autorisation</option>
                    <option value="Document">Document</option>
                    <option value="Information">Demande d'information</option>
                    <option value="Plainte">Plainte</option>
                    <option value="Autre">Autre</option>
                </select>
                @error('type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Priorité</label>
                <select name="priorite" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="normale">Normale</option>
                    <option value="basse">Basse</option>
                    <option value="haute">Haute</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Description</label>
                <textarea name="description" rows="8" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Soumettre la demande
                </button>
                <a href="{{ route('citoyen.demandes.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-2 px-6 rounded">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
