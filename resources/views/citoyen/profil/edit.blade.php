@extends('layouts.app')

@section('title', 'Éditer mon profil - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900">Mon Profil</h1>
    <p class="text-gray-600 mt-2">Complétez vos informations personnelles</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('citoyen.profil.update') }}" method="POST">
            @csrf
            @method('PATCH')
            
            <!-- Informations de compte -->
            <div class="mb-8 pb-8 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Informations de compte</h3>
                
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Nom complet</label>
                    <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                        @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Email</label>
                    <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                        @error('email') border-red-500 @enderror">
                    @error('email')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Informations personnelles -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-gray-900 mb-6">Informations personnelles</h3>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Date de naissance</label>
                        <input type="date" name="date_naissance" value="{{ old('date_naissance', auth()->user()->profil?->date_naissance) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Téléphone</label>
                        <input type="tel" name="telephone" value="{{ old('telephone', auth()->user()->profil?->telephone) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Adresse</label>
                    <input type="text" name="adresse" value="{{ old('adresse', auth()->user()->profil?->adresse) }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville', auth()->user()->profil?->ville) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Code postal</label>
                        <input type="text" name="code_postal" value="{{ old('code_postal', auth()->user()->profil?->code_postal) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Numéro d'identification</label>
                        <input type="text" name="num_id" value="{{ old('num_id', auth()->user()->profil?->num_id) }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Type d'ID</label>
                        <select name="type_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Sélectionner</option>
                            <option value="CNI" @selected(auth()->user()->profil?->type_id === 'CNI')>CNI</option>
                            <option value="Passeport" @selected(auth()->user()->profil?->type_id === 'Passeport')>Passeport</option>
                            <option value="Permis" @selected(auth()->user()->profil?->type_id === 'Permis')>Permis</option>
                        </select>
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">Biographie/Notes personnelles</label>
                    <textarea name="bio" rows="4" placeholder="Parlez un peu de vous..."
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">{{ old('bio', auth()->user()->profil?->bio) }}</textarea>
                </div>
            </div>

            <div class="flex space-x-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Enregistrer les modifications
                </button>
                <a href="{{ route('citoyen.dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-2 px-6 rounded">
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
