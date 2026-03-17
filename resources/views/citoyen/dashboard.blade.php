@extends('layouts.app')

@section('title', 'Dashboard Citoyen - Mairi')

@section('content')
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-900">Mon Dashboard Citoyen</h1>
    <p class="text-gray-600 mt-2">Gérez vos demandes et messages</p>
</div>

<!-- Statistiques -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Demandes Totales</h3>
        <p class="text-4xl font-bold text-blue-600">{{ $demandes->total() }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Pendantes</h3>
        <p class="text-4xl font-bold text-yellow-600">{{ $demandesPendantes }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Acceptées</h3>
        <p class="text-4xl font-bold text-green-600">{{ $demandesAcceptees }}</p>
    </div>
    
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-gray-600 text-sm font-semibold mb-2">Rejetées</h3>
        <p class="text-4xl font-bold text-red-600">{{ $demandesRejetees }}</p>
    </div>
</div>

<!-- Bouton nouvelle demande -->
<div class="mb-6">
    <a href="{{ route('citoyen.demandes.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        + Nouvelle demande
    </a>
</div>

<!-- Mes Messages -->
@if($messagesRecus->count() > 0)
<div class="bg-white rounded-lg shadow mb-8">
    <div class="p-6 border-b border-gray-200 bg-blue-50">
        <h2 class="text-xl font-bold text-gray-900">📬 Mes Messages ({{ $messagesRecus->count() }} dernier(s))</h2>
    </div>
    
    <div class="divide-y divide-gray-200">
        @foreach($messagesRecus as $message)
            <div class="p-6 hover:bg-gray-50 transition">
                <div class="flex justify-between items-start mb-2">
                    <h3 class="font-semibold text-gray-900">
                        <span class="text-blue-600">Demande:</span> 
                        <a href="{{ route('citoyen.demandes.show', $message->demande) }}" class="hover:underline">
                            {{ $message->demande->titre }}
                        </a>
                    </h3>
                </div>
                <p class="text-gray-700 text-sm mb-2">{{ Str::limit($message->contenu, 150) }}</p>
                <div class="flex justify-between items-center text-sm text-gray-500">
                    <span>De: <strong>{{ $message->expediteur->name }}</strong></span>
                    <span>{{ $message->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        @endforeach
    </div>
    
    @if($messagesRecus->count() > 0)
    <div class="p-4 bg-gray-50 border-t border-gray-200 text-center">
        <a href="{{ route('citoyen.demandes.index') }}" class="text-blue-600 hover:underline text-sm">
            Voir tous les messages dans mes demandes →
        </a>
    </div>
    @endif
</div>
@endif

<!-- Mes demandes -->
<div class="bg-white rounded-lg shadow">
    <div class="p-6 border-b border-gray-200">
        <h2 class="text-xl font-bold text-gray-900">Mes demandes</h2>
    </div>
    
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Titre</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Type</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Statut</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Priorité</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($demandes as $demande)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="px-6 py-4 text-gray-900 font-medium">{{ $demande->titre }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $demande->type }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block px-3 py-1 text-sm rounded-full 
                                @if($demande->statut === 'pendante') bg-yellow-100 text-yellow-800
                                @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                                @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                                @elseif($demande->statut === 'rejetee') bg-red-100 text-red-800
                                @endif">
                                {{ ucfirst($demande->statut) }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-semibold 
                                @if($demande->priorite === 'urgente') text-red-600
                                @elseif($demande->priorite === 'haute') text-orange-600
                                @elseif($demande->priorite === 'normale') text-blue-600
                                @else text-gray-600
                                @endif">
                                {{ ucfirst($demande->priorite) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-700 text-sm">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            <a href="{{ route('citoyen.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-700">Aucune demande. <a href="{{ route('citoyen.demandes.create') }}" class="text-blue-600">Créez-en une</a></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($demandes->hasPages())
        <div class="p-6 border-t border-gray-200">
            {{ $demandes->links() }}
        </div>
    @endif
</div>
@endsection
