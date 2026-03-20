@extends('layouts.app')

@section('title', 'Détail demande - Agent - Mairi')

@section('content')
<div class="mb-8">
    <a href="{{ route('agent.demandes.index') }}" class="text-blue-600 hover:underline mb-4 inline-block">
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
            
            <div class="grid grid-cols-2 gap-6 mb-6">
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
            </div>

            <div class="grid grid-cols-2 gap-6 pb-6 border-b border-gray-200">
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
            </div>

            <!-- Info Citoyen -->
            <div class="mt-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Informations du citoyen</h3>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <p class="font-semibold text-gray-900">{{ $demande->citoyen->name }}</p>
                    <p class="text-gray-700">{{ $demande->citoyen->email }}</p>
                    @if($demande->citoyen->profil)
                        <p class="text-gray-700">{{ $demande->citoyen->profil->telephone }}</p>
                    @endif
                </div>
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
            
            <div class="space-y-4 mb-6 max-h-96 overflow-y-auto">
                @forelse($demande->messages as $message)
                    <div class="border-l-4 @if($message->type_expediteur === 'citoyen') border-blue-500 @else border-green-500 @endif pl-4 py-2">
                        <p class="text-sm font-semibold text-gray-700">
                            {{ $message->expediteur->name }}
                            <span class="text-gray-500 text-xs">({{ ucfirst($message->type_expediteur) }})</span>
                            <span class="text-gray-500 font-normal text-xs">{{ $message->created_at->format('d/m/Y H:i') }}</span>
                        </p>
                        <p class="text-gray-900 mt-1 text-sm">{{ $message->contenu }}</p>
                    </div>
                @empty
                    <p class="text-gray-600">Aucun message pour le moment</p>
                @endforelse
            </div>

            <!-- Formulaire d'ajout de message -->
            @if($demande->statut === 'en_cours')
            <form action="{{ route('agent.messages.store', $demande) }}" method="POST" class="border-t border-gray-200 pt-6">
                @csrf
                
                <textarea name="contenu" rows="3" placeholder="Ajouter un message au citoyen..." required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent
                    @error('contenu') border-red-500 @enderror"></textarea>
                
                @error('contenu')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
                
                <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Envoyer le message
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Actions latérales -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow p-6 sticky top-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Actions</h2>
            
            <div class="space-y-3">
                @if($demande->statut === 'pendante')
                    <form action="{{ route('agent.demandes.assigner', $demande) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition">
                            ✓ Assigner à moi
                        </button>
                    </form>
                @elseif($demande->statut === 'en_cours')
                    <form action="{{ route('agent.demandes.accepter', $demande) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition">
                            ✓ Accepter
                        </button>
                    </form>
                    
                    <button type="button" onclick="document.getElementById('rejet-form').style.display='block'" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition">
                        ✗ Rejeter
                    </button>
                @endif
            </div>

            <!-- Formulaire de rejet caché -->
            @if($demande->statut === 'en_cours')
            <div id="rejet-form" style="display:none" class="mt-6 pt-6 border-t border-gray-200">
                <form action="{{ route('agent.demandes.rejeter', $demande) }}" method="POST">
                    @csrf
                    
                    <p class="text-sm font-semibold text-gray-700 mb-3">Motif de rejet</p>
                    <textarea name="motif_rejet" rows="4" required placeholder="Expliquer le motif du rejet..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm
                        @error('motif_rejet') border-red-500 @enderror"></textarea>
                    
                    @error('motif_rejet')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    
                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition text-sm">
                            Confirmer rejet
                        </button>
                        <button type="button" onclick="document.getElementById('rejet-form').style.display='none'" 
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-900 font-bold py-2 px-4 rounded transition text-sm">
                            Annuler
                        </button>
                    </div>
                </form>
            </div>
            @endif

            <!-- Informations -->
            <div class="mt-6 pt-6 border-t border-gray-200 space-y-4">
                <div>
                    <p class="text-gray-600 text-sm font-semibold">ID Demande</p>
                    <p class="text-gray-900 font-mono text-sm">#{{ $demande->id }}</p>
                </div>
                
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Créée le</p>
                    <p class="text-gray-900 text-sm">{{ $demande->created_at->format('d/m/Y H:i') }}</p>
                </div>
                
                @if($demande->date_limite)
                <div>
                    <p class="text-gray-600 text-sm font-semibold">Date limite</p>
                    <p class="text-gray-900 text-sm {{ now()->isAfter($demande->date_limite) ? 'text-red-600 font-bold' : '' }}">
                        {{ $demande->date_limite->format('d/m/Y') }}
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
