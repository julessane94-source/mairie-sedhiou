@extends('layouts.app')

@section('title', 'Rapport de présence')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.pointage.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">📊 Rapport de présence</h1>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Agent</label>
                <select name="agent_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                    <option value="">-- Tous les agents --</option>
                    @foreach($agents ?? [] as $agent)
                        <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                            {{ $agent->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Depuis</label>
                <input type="date" name="from_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ request('from_date') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Jusqu'au</label>
                <input type="date" name="to_date" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ request('to_date') }}">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">🔍 Filtrer</button>
                <a href="{{ route('admin.pointage.rapport') }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold">↻ Réinitialiser</a>
            </div>
        </form>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">📅 Jours ouvrables</p>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['jours_ouvrables'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">🟢 Présences</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['presences'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">🔴 Absences</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['absences'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">✓ Justifiées</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['justifiees'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">📊 Taux moyen</p>
            <p class="text-2xl font-bold text-indigo-600">{{ $stats['taux_moyen'] ?? 0 }}%</p>
        </div>
    </div>

    <!-- Tableau récapitulatif par agent -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">👤 Agent</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">📅 Jours</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">🟢 Présences</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">🔴 Absences</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">✓ Justifiées</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">⏳ Retards</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">📊 Taux</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-900">📋 Détails</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($agentStats ?? [] as $stat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $stat['agent_name'] ?? 'N/A' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $stat['jours'] ?? 0 }}</td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="inline-block px-3 py-1 rounded-full bg-green-100 text-green-900 font-semibold text-xs">
                                {{ $stat['presences'] ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="inline-block px-3 py-1 rounded-full bg-red-100 text-red-900 font-semibold text-xs">
                                {{ $stat['absences'] ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-900 font-semibold text-xs">
                                {{ $stat['justifiees'] ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="inline-block px-3 py-1 rounded-full bg-yellow-100 text-yellow-900 font-semibold text-xs">
                                {{ $stat['retards'] ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <span class="font-bold text-indigo-600">{{ $stat['taux_presence'] ?? 0 }}%</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-center">
                            <a href="{{ route('admin.pointage.show', $stat['agent_id'] ?? '#') }}" class="text-blue-600 hover:underline text-sm font-semibold">
                                👁️ Voir
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            Aucune donnée disponible pour les filtres sélectionnés
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Graphique de tendance (optionnel avec Chart.js) -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Tendance de présence</h3>
            <canvas id="attendanceChart"></canvas>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Distribution</h3>
            <canvas id="distributionChart"></canvas>
        </div>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex gap-4">
        <button onclick="window.print()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 font-semibold">
            🖨️ Imprimer
        </button>
        <a href="" download class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
            📥 Exporter CSV
        </a>
        <a href="" download class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
            📄 Exporter PDF
        </a>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3/dist/chart.min.js"></script>
<script>
// Chart pour la tendance de présence
const attendanceCtx = document.getElementById('attendanceChart')?.getContext('2d');
if (attendanceCtx) {
    new Chart(attendanceCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($chartData['labels'] ?? []) !!},
            datasets: [
                {
                    label: 'Présences',
                    data: {!! json_encode($chartData['presences'] ?? []) !!},
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Absences',
                    data: {!! json_encode($chartData['absences'] ?? []) !!},
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
}

// Chart pour la distribution
const distributionCtx = document.getElementById('distributionChart')?.getContext('2d');
if (distributionCtx) {
    new Chart(distributionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Présences', 'Absences', 'Justifiées', 'Retards'],
            datasets: [{
                data: {!! json_encode([
                    $stats['presences'] ?? 0,
                    $stats['absences'] ?? 0,
                    $stats['justifiees'] ?? 0,
                    $stats['retards'] ?? 0
                ]) !!},
                backgroundColor: [
                    '#10b981',
                    '#ef4444',
                    '#3b82f6',
                    '#f59e0b'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true, position: 'bottom' }
            }
        }
    });
}
</script>
@endpush
@endsection
