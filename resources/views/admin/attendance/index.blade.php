@extends('layouts.app')

@section('title', 'Pointage des Agents')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">⏰ Gestion du pointage</h1>
    <p class="text-gray-600 mt-1">Calendrier du mois pour la présence des agents</p>
</div>

<!-- Sélection du mois -->
<div class="bg-white rounded-lg shadow p-4 mb-6">
    <form action="{{ route('admin.pointage.index') }}" method="GET" class="flex gap-4 items-end">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Année</label>
            <select name="year" class="px-4 py-2 border border-gray-300 rounded-lg">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                <option value="{{ $y }}" {{ $y === $year ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Mois</label>
            <select name="month" class="px-4 py-2 border border-gray-300 rounded-lg">
                @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $m === $month ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::createFromDate(now()->year, $m, 1)->format('F (m)') }}
                </option>
                @endfor
            </select>
        </div>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Filtrer
        </button>
        <a href="{{ route('admin.pointage.rapport') }}?month={{ $month }}&year={{ $year }}" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
            📊 Rapport
        </a>
    </form>
</div>

<!-- Calendrier -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-gray-700" style="min-width: 150px;">Agent</th>
                    @for($d = 1; $d <= $datesOfMonth; $d++)
                    <th class="px-2 py-2 text-center font-semibold text-gray-700 text-xs" style="min-width: 40px;">
                        <div class="text-gray-600">{{ $d }}</div>
                        <div class="text-xs text-gray-400">
                            @php
                            $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                            echo $date->format('D')[0];
                            @endphp
                        </div>
                    </th>
                    @endfor
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($agents as $agent)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-semibold text-gray-900">
                        <a href="{{ route('admin.agents.show', $agent) }}" class="text-blue-600 hover:underline">
                            {{ $agent->nom }}
                        </a>
                    </td>
                    @for($d = 1; $d <= $datesOfMonth; $d++)
                    <td class="px-2 py-3 text-center text-xs">
                        @php
                        $date = \Carbon\Carbon::createFromDate($year, $month, $d);
                        $attendance = $attendances->get($agent->id)?->where('date_presence', $date->toDateString())->first();
                        @endphp
                        
                        @if($attendance)
                            <a href="{{ route('admin.pointage.show', $agent) }}" class="inline-block px-2 py-1 rounded-full text-white font-semibold
                                @if($attendance->statut === 'present') bg-green-500
                                @elseif($attendance->statut === 'absent') bg-red-500
                                @elseif($attendance->statut === 'congé') bg-blue-500
                                @elseif($attendance->statut === 'retard') bg-yellow-500
                                @else bg-gray-500
                                @endif"
                                title="{{ $attendance->statut }}">
                                @if($attendance->statut === 'present') ✓
                                @elseif($attendance->statut === 'absent') ✗
                                @elseif($attendance->statut === 'congé') C
                                @elseif($attendance->statut === 'retard') R
                                @else -
                                @endif
                            </a>
                        @else
                            <button formaction="{{ route('admin.pointage.marquer', $agent) }}" 
                                    class="inline-block w-6 h-6 border-2 border-gray-300 rounded hover:border-blue-500 hover:bg-blue-50 text-xs text-gray-400"
                                    title="Marquer la présence">
                                —
                            </button>
                        @endif
                    </td>
                    @endfor
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $datesOfMonth + 1 }}" class="px-4 py-8 text-center text-gray-500">
                        Aucun agent trouvé
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Légende -->
<div class="mt-6 bg-white rounded-lg shadow p-4">
    <h4 class="font-semibold text-gray-900 mb-3">Légende</h4>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="flex items-center gap-2">
            <span class="inline-block w-6 h-6 bg-green-500 rounded text-white text-center text-sm font-bold">✓</span>
            <span class="text-sm text-gray-700">Présent</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-6 h-6 bg-red-500 rounded text-white text-center text-sm font-bold">✗</span>
            <span class="text-sm text-gray-700">Absent</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-6 h-6 bg-blue-500 rounded text-white text-center text-sm font-bold">C</span>
            <span class="text-sm text-gray-700">Congé</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-6 h-6 bg-yellow-500 rounded text-white text-center text-sm font-bold">R</span>
            <span class="text-sm text-gray-700">Retard</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-6 h-6 border-2 border-gray-300 rounded text-gray-400 text-center text-sm font-bold">—</span>
            <span class="text-sm text-gray-700">Non marqué</span>
        </div>
    </div>
</div>

<!-- INFO -->
<div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <p class="text-sm text-blue-800">
        💡 <strong>Info:</strong> Cliquez sur le nom d'un agent pour voir son détail ou marquer sa présence pour des jours spécifiques.
    </p>
</div>
@endsection
