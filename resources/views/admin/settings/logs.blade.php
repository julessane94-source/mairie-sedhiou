@extends('layouts.app')

@section('title', 'Paramètres - Journaux')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="{{ route('admin.settings.index') }}" class="text-blue-600 hover:underline">← Retour</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">📋 Journaux système</h1>
        </div>
        <form action="{{ route('admin.settings.logs.clear') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir effacer tous les logs?')">
            @csrf
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                🗑️ Effacer les logs
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">🟢 Opérations réussies</p>
            <p class="text-2xl font-bold text-green-600">{{ $stats['success'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">🟡 Avertissements</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $stats['warning'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">🔴 Erreurs</p>
            <p class="text-2xl font-bold text-red-600">{{ $stats['error'] ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-gray-600 text-sm">📊 Total</p>
            <p class="text-2xl font-bold text-blue-600">{{ $stats['total'] ?? 0 }}</p>
        </div>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <input type="search" id="searchLogs" placeholder="Rechercher..." class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <select id="filterLevel" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Tous les niveaux</option>
                    <option value="success">✓ Succès</option>
                    <option value="warning">⚠ Avertissement</option>
                    <option value="error">✗ Erreur</option>
                    <option value="info">ℹ Info</option>
                </select>
            </div>
            <div>
                <select id="filterType" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Tous les types</option>
                    <option value="auth">Authentification</option>
                    <option value="demande">Demande</option>
                    <option value="payment">Paiement</option>
                    <option value="agent">Agent</option>
                    <option value="system">Système</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table des logs -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-100 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">⏰ Date/Heure</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Niveau</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-900">Utilisateur</th>
                </tr>
            </thead>
            <tbody id="logsTable" class="divide-y divide-gray-200">
                @forelse($logs ?? [] as $log)
                    <tr class="hover:bg-gray-50 log-row" data-level="{{ strtolower($log['level'] ?? '') }}" data-type="{{ $log['type'] ?? '' }}">
                        <td class="px-6 py-3 text-xs text-gray-600 whitespace-nowrap">{{ $log['datetime'] ?? '-' }}</td>
                        <td class="px-6 py-3 text-xs">
                            <span class="inline-block px-2 py-1 rounded text-white font-semibold
                                {{ $log['type'] === 'auth' ? 'bg-blue-500' : '' }}
                                {{ $log['type'] === 'demande' ? 'bg-purple-500' : '' }}
                                {{ $log['type'] === 'payment' ? 'bg-green-500' : '' }}
                                {{ $log['type'] === 'agent' ? 'bg-orange-500' : '' }}
                                {{ $log['type'] === 'system' ? 'bg-gray-500' : '' }}
                            ">
                                {{ ucfirst($log['type'] ?? 'Autre') }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs">
                            <span class="inline-block px-2 py-1 rounded font-semibold
                                {{ $log['level'] === 'success' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log['level'] === 'warning' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $log['level'] === 'error' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $log['level'] === 'info' ? 'bg-blue-100 text-blue-800' : '' }}
                            ">
                                {{ isset($log['level']) ? match($log['level']) {
                                    'success' => '✓ Succès',
                                    'warning' => '⚠ Avertissement',
                                    'error' => '✗ Erreur',
                                    'info' => 'ℹ Info',
                                    default => ucfirst($log['level'])
                                } : '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-700 max-w-xs truncate" title="{{ $log['message'] ?? '' }}">
                            {{ $log['message'] ?? '-' }}
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-600">
                            {{ $log['user'] ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            Aucun log disponible
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination (optionnel) -->
    @if(isset($logs) && is_object($logs) && method_exists($logs, 'links'))
        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    @endif
</div>

<style>
    .hidden-row {
        display: none;
    }
</style>

<script>
    document.getElementById('searchLogs')?.addEventListener('keyup', filterLogs);
    document.getElementById('filterLevel')?.addEventListener('change', filterLogs);
    document.getElementById('filterType')?.addEventListener('change', filterLogs);

    function filterLogs() {
        const search = document.getElementById('searchLogs')?.value.toLowerCase() || '';
        const level = document.getElementById('filterLevel')?.value || '';
        const type = document.getElementById('filterType')?.value || '';

        document.querySelectorAll('.log-row').forEach(row => {
            const message = row.textContent.toLowerCase();
            const rowLevel = row.dataset.level;
            const rowType = row.dataset.type;

            const matchSearch = message.includes(search);
            const matchLevel = !level || rowLevel === level;
            const matchType = !type || rowType === type;

            row.classList.toggle('hidden-row', !(matchSearch && matchLevel && matchType));
        });
    }
</script>
@endsection
