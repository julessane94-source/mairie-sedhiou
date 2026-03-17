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
                <select name="type" id="type_demande" required 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('type') border-red-500 @enderror">
                    <option value="">-- Sélectionnez une option --</option>
                    @foreach($typesDemandes as $categorie => $types)
                        <optgroup label="{{ $categorie }}">
                            @foreach($types as $type)
                                <option value="{{ $type['value'] }}" 
                                        data-delai="{{ $type['delai'] }}" 
                                        data-frais="{{ $type['frais'] }}">
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <!-- Informations du type sélectionné -->
                <div id="type-info" class="mt-3 p-3 bg-blue-50 rounded-lg hidden">
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="font-semibold text-blue-900">Délai estimé:</span>
                            <span id="delai-info" class="text-blue-700"></span>
                        </div>
                        <div>
                            <span class="font-semibold text-blue-900">Frais:</span>
                            <span id="frais-info" class="text-blue-700"></span>
                        </div>
                    </div>
                </div>
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

<script>
document.getElementById('type_demande').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const delai = selectedOption.getAttribute('data-delai');
    const frais = selectedOption.getAttribute('data-frais');
    const typeInfo = document.getElementById('type-info');
    
    if (delai && frais) {
        document.getElementById('delai-info').textContent = delai + ' jours';
        document.getElementById('frais-info').textContent = frais + ' FCFA';
        typeInfo.classList.remove('hidden');
    } else {
        typeInfo.classList.add('hidden');
    }
});
</script>
@endsection
