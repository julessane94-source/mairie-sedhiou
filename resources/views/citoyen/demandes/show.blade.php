@extends('layouts.app')

@section('title', 'Détail demande - Mairi')

@section('content')
<div class="mb-8">
    <a href="{{ route('citoyen.demandes.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
        ← Retour aux demandes
    </a>
    <h1 class="text-3xl font-bold text-gray-900">{{ $demande->titre }}</h1>
    <p class="text-gray-600 mt-2">Créée le {{ $demande->created_at->format('d/m/Y à H:i') }}</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Contenu principal -->
    <div class="lg:col-span-2">
        <!-- Détails -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Informations</h2>
            
            <div class="grid grid-cols-2 gap-6">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Type</p>
                    <p class="text-gray-900 font-medium">{{ $demande->type }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Priorité</p>
                    <span class="inline-block px-3 py-1 text-sm rounded-full font-semibold
                        @if($demande->priorite === 'urgente') bg-red-100 text-red-800
                        @elseif($demande->priorite === 'haute') bg-orange-100 text-orange-800
                        @elseif($demande->priorite === 'normale') bg-blue-100 text-blue-800
                        @else bg-gray-100 text-gray-800
                        @endif">
                        {{ ucfirst($demande->priorite) }}
                    </span>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Statut</p>
                    <span class="inline-block px-3 py-1 text-sm rounded-full font-semibold
                        @if($demande->statut === 'pendante') bg-yellow-100 text-yellow-800
                        @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                        @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                        @elseif($demande->statut === 'rejetee') bg-red-100 text-red-800
                        @endif">
                        {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                    </span>
                </div>
                
                @if($demande->agentAssigne)
                <div class="col-span-2">
                    <p class="text-gray-600 text-sm font-semibold">Agent assigné</p>
                    <p class="text-gray-900 font-medium">{{ $demande->agentAssigne->name }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Description -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Description</h2>
            <p class="text-gray-700 whitespace-pre-wrap">{{ $demande->description }}</p>
        </div>

        @if($demande->statut === 'rejetee' && $demande->motif_rejet)
        <div class="bg-red-50 border-l-4 border-red-600 p-6 mb-6">
            <h3 class="text-lg font-bold text-red-800 mb-2">Motif de rejet</h3>
            <p class="text-red-700">{{ $demande->motif_rejet }}</p>
        </div>
        @endif

        <!-- Messages -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-6">Messages ({{ $demande->messages->count() }})</h2>
            
            <div class="space-y-4 mb-6">
                @forelse($demande->messages as $message)
                    <div class="border-l-4 @if($message->type_expediteur === 'citoyen') border-blue-500 pl-4 @else border-green-500 pl-4 @endif">
                        <p class="text-sm font-semibold text-gray-700">
                            {{ $message->expediteur->name }}
                            <span class="text-gray-500 font-normal">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                        </p>
                        <p class="text-gray-900 mt-1">{{ $message->contenu }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">Aucun message pour le moment</p>
                @endforelse
            </div>

            <!-- Formulaire d'ajout de message -->
            @if($demande->statut !== 'rejetee' && $demande->statut !== 'acceptee')
            <form action="{{ route('citoyen.messages.store', $demande) }}" method="POST" class="border-t border-gray-200 pt-6">
                @csrf
                
                <textarea name="contenu" rows="4" placeholder="Ajouter un message..." required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('contenu') border-red-500 @enderror"></textarea>
                
                @error('contenu')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Envoyer le message
                </button>
            </form>
            @else
            <p class="text-gray-600 text-sm italic">Les messages sont fermés pour cette demande</p>
            @endif
        </div>
    </div>

    <!-- Barre latérale -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Résumé</h2>
            
            <div class="space-y-4">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">ID Demande</p>
                    <p class="text-gray-900 font-mono text-sm">#{{ $demande->id }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Créée le</p>
                    <p class="text-gray-900">{{ $demande->created_at->format('d/m/Y') }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Mise à jour</p>
                    <p class="text-gray-900">{{ $demande->updated_at->format('d/m/Y H:i') }}</p>
                </div>
                
                @if($demande->date_limite)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Date limite</p>
                    <p class="text-gray-900">{{ $demande->date_limite->format('d/m/Y') }}</p>
                </div>
                @endif
                
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-gray-600 text-sm font-semibold mb-2">Statut</p>
                    <div class="space-y-2">
                        <p class="text-sm text-gray-700">
                            @if($demande->statut === 'pendante')
                            En attente d'assignment
                            @elseif($demande->statut === 'en_cours')
                            En cours de traitement
                            @elseif($demande->statut === 'acceptee')
                            ✓ Acceptée
                            @elseif($demande->statut === 'rejetee')
                            ✗ Rejetée
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
