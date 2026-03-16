@extends('layouts.app')

@section('title', "Présence de " . ($agent->name ?? 'Agent'))

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.pointage.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">📋 Détails de présence</h1>
    </div>

    <!-- Agent Header -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $agent->name ?? 'N/A' }}</h2>
                <p class="text-gray-600">{{ $agent->email ?? 'N/A' }} • {{ $agent->specialite ?? 'N/A' }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-600">Statut</p>
                <span class="inline-block px-3 py-1 rounded-full font-semibold text-white
                    {{ $agent->statut === 'actif' ? 'bg-green-500' : '' }}
                    {{ $agent->statut === 'inactif' ? 'bg-gray-500' : '' }}
                    {{ $agent->statut === 'congé' ? 'bg-blue-500' : '' }}
                    {{ $agent->statut === 'suspendu' ? 'bg-red-500' : '' }}
                ">
                    {{ ucfirst($agent->statut ?? 'inconnu') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Statistiques du mois -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Présences</p>
            <p class="text-2xl font-bold text-green-600">{{ count($attendances->where('statut', 'present')) ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Absences</p>
            <p class="text-2xl font-bold text-red-600">{{ count($attendances->where('statut', 'absent')) ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Absences justifiées</p>
            <p class="text-2xl font-bold text-blue-600">{{ count($attendances->where('justifiee', true)) ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">Taux de présence</p>
            @php
            $total = count($attendances) > 0 ? count($attendances) : 1;
            $presence = count($attendances->where('statut', 'present'));
            $taux = round(($presence / $total) * 100);
            @endphp
            <p class="text-2xl font-bold text-indigo-600">{{ $taux }}%</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="flex gap-4">
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mois</label>
                <form method="GET" class="flex gap-2">
                    <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Filtrer</button>
                </form>
            </div>
            <div class="flex-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Exporter</label>
                <a href="{{ route('admin.pointage.rapport') }}?agent_id={{ $agent->id }}" class="block px-4 py-2 bg-green-600 text-white text-center rounded-lg hover:bg-green-700">📥 Exporter en PDF</a>
            </div>
        </div>
    </div>

    <!-- Tableau des présences -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">📅 Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Heure d'arrivée</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Heure de départ</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Heures travaillées</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($attendances as $attendance)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $attendance->date->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-block px-3 py-1 rounded-full font-semibold text-white text-xs
                                {{ $attendance->statut === 'present' ? 'bg-green-500' : '' }}
                                {{ $attendance->statut === 'absent' ? 'bg-red-500' : '' }}
                                {{ $attendance->statut === 'congé' ? 'bg-blue-500' : '' }}
                                {{ $attendance->statut === 'retard' ? 'bg-yellow-500' : '' }}
                            ">
                                {{ ucfirst($attendance->statut) }}
                            </span>
                            @if($attendance->justifiee)
                                <span class="ml-2 text-xs text-blue-600">✓ Justifiée</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $attendance->check_in ? $attendance->check_in->format('H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $attendance->check_out ? $attendance->check_out->format('H:i') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $attendance->heures_travaillees ?? '0' }}h
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if(!$attendance->justifiee && $attendance->statut === 'absent')
                                <form action="{{ route('admin.pointage.justifier', $attendance->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="text" name="motif" placeholder="Motif" class="px-2 py-1 text-xs border rounded" required>
                                    <button type="submit" class="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700">Justifier</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-600">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Aucune présence enregistrée pour cette période
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Notes de justification -->
    @if($attendances->where('motif_absence', '!=', null)->count() > 0)
    <div class="bg-blue-50 rounded-lg border border-blue-200 p-4 mt-6">
        <h3 class="font-semibold text-blue-900 mb-3">📝 Motifs d'absence enregistrés</h3>
        <ul class="space-y-2">
            @foreach($attendances->where('motif_absence', '!=', null) as $attendance)
                <li class="text-sm text-blue-800">
                    <strong>{{ $attendance->date->format('d/m/Y') }}:</strong> {{ $attendance->motif_absence }}
                </li>
            @endforeach
        </ul>
    </div>
    @endif
</div>
@endsection
