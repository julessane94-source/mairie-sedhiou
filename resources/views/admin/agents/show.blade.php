@extends('layouts.app')

@section('title', $agent->nom)

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.agents.index') }}" class="text-blue-600 hover:underline">← Retour à la liste</a>
</div>

<div class="grid grid-cols-3 gap-6">
    <!-- Fiche agent -->
    <div class="col-span-2">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $agent->nom }}</h1>
                    <p class="text-gray-600 mt-1">{{ $agent->email }}</p>
                </div>
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold
                    @if($agent->statut === 'actif') bg-green-100 text-green-800
                    @elseif($agent->statut === 'congé') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ ucfirst($agent->statut) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="text-sm text-gray-600">Téléphone</label>
                    <p class="text-gray-900 font-semibold">{{ $agent->telephone ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Spécialité</label>
                    <p class="text-gray-900 font-semibold">{{ $agent->specialite ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Adresse</label>
                    <p class="text-gray-900 font-semibold">{{ $agent->adresse ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Date d'embauche</label>
                    <p class="text-gray-900 font-semibold">{{ $agent->date_embauche ?  $agent->date_embauche->format('d/m/Y') : 'N/A' }}</p>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <a href="{{ route('admin.agents.edit', $agent) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    ✏️ Éditer
                </a>
            </div>
        </div>

        <!-- Demandes assignées -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-4">📋 Demandes assignées ({{ $stats['demandes_assignees'] }})</h3>
            
            @if($agent->demandes->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Titre</th>
                            <th class="px-4 py-2 text-left">Priorité</th>
                            <th class="px-4 py-2 text-left">Statut</th>
                            <th class="px-4 py-2 text-left">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach($agent->demandes as $demande)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-gray-900">{{ Str::limit($demande->titre, 30) }}</td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($demande->priorite === 'urgente') bg-red-100 text-red-800
                                    @elseif($demande->priorite === 'haute') bg-orange-100 text-orange-800
                                    @elseif($demande->priorite === 'normale') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($demande->priorite) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                    @if($demande->statut === 'pendante') bg-yellow-100 text-yellow-800
                                    @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800
                                    @elseif($demande->statut === 'acceptee') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ ucfirst($demande->statut) }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                <a href="{{ route('admin.demandes.show', $demande) }}" class="text-blue-600 hover:underline">Voir</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-center text-gray-500 py-4">Aucune demande assignée</p>
            @endif
        </div>
    </div>

    <!-- Statistiques -->
    <div class="col-span-1">
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">📊 Statistiques</h3>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-gray-600">Demandes:</span>
                    <span class="font-bold text-blue-600">{{ $stats['demandes_assignees'] }}</span>
                </div>
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-gray-600">En cours:</span>
                    <span class="font-bold text-yellow-600">{{ $stats['demandes_en_cours'] }}</span>
                </div>
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-gray-600">Acceptées:</span>
                    <span class="font-bold text-green-600">{{ $stats['demandes_acceptees'] }}</span>
                </div>
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-gray-600">Rejetées:</span>
                    <span class="font-bold text-red-600">{{ $stats['demandes_rejetees'] }}</span>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-200">
                <h4 class="font-semibold text-gray-900 mb-3">Présence du mois</h4>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">✅ Présent:</span>
                        <span class="font-bold text-green-600">{{ $stats['presence_mois'] }} jours</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">❌ Absent:</span>
                        <span class="font-bold text-red-600">{{ $stats['absence_mois'] }} jours</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">⚡ Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.pointage.show', $agent) }}" class="block w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-center text-sm font-semibold">
                    📅 Voir pointage
                </a>
                <a href="{{ route('admin.agents.edit', $agent) }}" class="block w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-center text-sm font-semibold">
                    ✏️ Éditer
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
