@extends('layouts.app')

@section('title', "Modifier la demande")

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.demandes.show', $demande->id) }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">✏️ Modifier la demande</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Formulaire principal -->
        <div class="md:col-span-2">
            <form action="{{ route('admin.demandes.update', $demande->id) }}" method="POST" class="bg-white rounded-lg shadow p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Informations générales -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Titre *</label>
                    <input type="text" name="titre" value="{{ old('titre', $demande->titre) }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('titre') border-red-500 @enderror" required>
                    @error('titre')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description *</label>
                    <textarea name="description" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-lg @error('description') border-red-500 @enderror" required>{{ old('description', $demande->description) }}</textarea>
                    @error('description')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Statut *</label>
                        <select name="statut" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            <option value="en_attente" {{ old('statut', $demande->statut) === 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="en_cours" {{ old('statut', $demande->statut) === 'en_cours' ? 'selected' : '' }}>En cours</option>
                            <option value="acceptée" {{ old('statut', $demande->statut) === 'acceptée' ? 'selected' : '' }}>Acceptée</option>
                            <option value="rejetée" {{ old('statut', $demande->statut) === 'rejetée' ? 'selected' : '' }}>Rejetée</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Priorité *</label>
                        <select name="priorite" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required>
                            <option value="basse" {{ old('priorite', $demande->priorite) === 'basse' ? 'selected' : '' }}>Basse</option>
                            <option value="moyenne" {{ old('priorite', $demande->priorite) === 'moyenne' ? 'selected' : '' }}>Moyenne</option>
                            <option value="haute" {{ old('priorite', $demande->priorite) === 'haute' ? 'selected' : '' }}>Haute</option>
                        </select>
                    </div>
                </div>

                <!-- Affectation -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">👤 Affectation</h3>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Agent assigné</label>
                        <select name="agent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">-- Aucun agent --</option>
                            @foreach($agents ?? [] as $agent)
                                <option value="{{ $agent->id }}" {{ old('agent_id', $demande->agent_id) == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }} ({{ $agent->specialite ?? 'N/A' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Notes internes -->
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📝 Notes internes</h3>

                    <textarea name="notes_internes" rows="4" placeholder="Notes visibles uniquement par les administrateurs" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('notes_internes', $demande->notes_internes ?? '') }}</textarea>
                </div>

                <!-- Raison de rejet -->
                @if($demande->statut === 'rejetée')
                <div class="border-t border-gray-200 pt-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">❌ Raison du rejet</h3>

                    <textarea name="motif_rejet" rows="3" placeholder="Expliquez pourquoi cette demande a été rejetée" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ old('motif_rejet', $demande->motif_rejet ?? '') }}</textarea>
                </div>
                @endif

                <!-- Boutons d'action -->
                <div class="pt-4 border-t border-gray-200 flex gap-3">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                        💾 Enregistrer les modifications
                    </button>
                    <a href="{{ route('admin.demandes.show', $demande->id) }}" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold">
                        ❌ Annuler
                    </a>
                </div>
            </form>
        </div>

        <!-- Sidebar avec infos -->
        <div>
            <div class="bg-white rounded-lg shadow p-6 sticky top-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Résumé</h3>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-600">Citoyen</dt>
                        <dd class="font-semibold text-gray-900">{{ $demande->citoyen->name ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-600">Email</dt>
                        <dd class="font-semibold text-gray-900 break-all">{{ $demande->citoyen->email ?? 'N/A' }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-600">Téléphone</dt>
                        <dd class="font-semibold text-gray-900">{{ $demande->citoyen->telephone ?? 'N/A' }}</dd>
                    </div>

                    <div class="border-t border-gray-200 pt-3">
                        <dt class="text-gray-600">Créée le</dt>
                        <dd class="font-semibold text-gray-900">{{ $demande->created_at->format('d/m/Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-gray-600">Modifiée le</dt>
                        <dd class="font-semibold text-gray-900">{{ $demande->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>

                <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-xs text-blue-900 font-semibold mb-2">💡 Conseil</p>
                    <p class="text-xs text-blue-800">Assurez-vous d'assigner cette demande à un agent compétent et mettez à jour régulièrement le statut.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
