@extends('layouts.app')

@section('title', 'Dashboard Admin - Mairi')

@section('content')
<div class="space-y-8">
    <section class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 p-8 text-white shadow-2xl">
        <div class="absolute -right-16 -top-16 h-56 w-56 rounded-full bg-cyan-300/20 blur-3xl"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-blue-400/20 blur-3xl"></div>
        <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="mb-2 inline-block rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">Centre de pilotage</p>
                <h1 class="text-3xl font-black tracking-tight sm:text-4xl">Tableau de bord Administrateur</h1>
                <p class="mt-2 max-w-2xl text-sm text-blue-100">Vue consolidée des opérations MAIRI avec alertes, charge des agents et activité des citoyens en temps réel.</p>
            </div>
            <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm">
                <p class="text-blue-100">Dernière mise à jour</p>
                <p class="font-bold">{{ now()->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </section>

    @if(count($diagnostics) > 0)
    <section class="rounded-3xl border border-slate-200 bg-white/95 p-6 shadow-sm backdrop-blur">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-extrabold text-slate-900">Diagnostics et alertes</h2>
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ count($diagnostics) }} alerte(s)</span>
        </div>
        <div class="space-y-3">
            @foreach($diagnostics as $diagnostic)
            <div class="rounded-2xl border p-4 @if($diagnostic['type'] === 'error') border-rose-200 bg-rose-50 @elseif($diagnostic['type'] === 'warning') border-amber-200 bg-amber-50 @else border-cyan-200 bg-cyan-50 @endif">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="font-bold text-slate-900">{{ $diagnostic['titre'] }}</h3>
                        <p class="mt-1 text-sm text-slate-700">{{ $diagnostic['message'] }}</p>
                    </div>
                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">{{ $diagnostic['action'] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    <section class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-blue-200 bg-gradient-to-br from-blue-600 to-cyan-600 p-6 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-blue-100">Utilisateurs</p>
            <p class="mt-3 text-4xl font-black">{{ $stats['utilisateurs']['total'] }}</p>
            <p class="mt-3 text-sm text-blue-100">Citoyens: {{ $stats['utilisateurs']['citoyens'] }} | Agents: {{ $stats['utilisateurs']['agents'] }} | Admins: {{ $stats['utilisateurs']['admins'] }}</p>
        </article>
        <article class="rounded-3xl border border-emerald-200 bg-gradient-to-br from-emerald-600 to-teal-600 p-6 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-100">Demandes</p>
            <p class="mt-3 text-4xl font-black">{{ $stats['demandes']['total'] }}</p>
            <p class="mt-3 text-sm text-emerald-100">En cours: {{ $stats['demandes']['en_cours'] }} | Pendantes: {{ $stats['demandes']['pendantes'] }} | Acceptées: {{ $stats['demandes']['acceptees'] }}</p>
        </article>
        <article class="rounded-3xl border border-sky-200 bg-gradient-to-br from-sky-600 to-indigo-600 p-6 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-100">Paiements</p>
            <p class="mt-3 text-4xl font-black">{{ number_format($stats['paiements']['total']) }}</p>
            <p class="mt-3 text-sm text-sky-100">En attente: {{ $stats['paiements']['pending'] }} | Payés: {{ $stats['paiements']['paid'] }} | Annulés: {{ $stats['paiements']['cancelled'] }}</p>
        </article>
        <article class="rounded-3xl border border-amber-200 bg-gradient-to-br from-amber-500 to-orange-600 p-6 text-white shadow-lg">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-amber-100">Présence</p>
            <p class="mt-3 text-4xl font-black">{{ $agentsPresents }}/{{ $agentsPresents + $agentsAbsents }}</p>
            <p class="mt-3 text-sm text-amber-100">Présents: {{ $agentsPresents }} | Absents: {{ $agentsAbsents }}</p>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-900">Performance</h2>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <span class="text-slate-600">Délai moyen</span>
                    <span class="font-bold text-blue-700">{{ $delaiMoyenTraitement }} jours</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <span class="text-slate-600">Taux de satisfaction</span>
                    <span class="font-bold text-emerald-700">{{ $tauxSatisfaction }}%</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                    <span class="text-slate-600">Messages non lus</span>
                    <span class="font-bold text-rose-700">{{ $stats['messages']['non_lus'] }}</span>
                </div>
            </div>
        </article>

        <article class="xl:col-span-2 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-extrabold text-slate-900">Top agents performants</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">
                            <th class="px-3 py-3">Agent</th>
                            <th class="px-3 py-3 text-center">Demandes</th>
                            <th class="px-3 py-3 text-center">Réussite</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agentStats->sortByDesc('taux_reussite')->take(5) as $stat)
                        <tr class="border-b border-slate-100 last:border-none">
                            <td class="px-3 py-3 text-slate-800">
                                <a href="{{ route('admin.agents.show', $stat['agent']) }}" class="font-semibold text-blue-700 hover:underline">{{ $stat['agent']->nom }}</a>
                            </td>
                            <td class="px-3 py-3 text-center text-slate-600">{{ $stat['demandes_total'] }}</td>
                            <td class="px-3 py-3 text-center">
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ $stat['taux_reussite'] }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="px-3 py-4 text-center text-slate-500">Aucun agent disponible.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4"><h3 class="font-extrabold text-slate-900">Derniers citoyens</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse($derniersCitoyens as $citoyen)
                <a href="{{ route('admin.utilisateurs.show', $citoyen) }}" class="block p-4 transition hover:bg-slate-50">
                    <p class="font-semibold text-slate-900">{{ $citoyen->nom }}</p>
                    <p class="text-xs text-slate-500">{{ $citoyen->email }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $citoyen->created_at->diffForHumans() }}</p>
                </a>
                @empty
                <div class="p-4 text-center text-sm text-slate-500">Aucun citoyen</div>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4"><h3 class="font-extrabold text-slate-900">Derniers paiements</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse($derniersPaiements as $payment)
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-900">{{ Str::limit($payment->reference_recu, 15) }}</p>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold @if($payment->statut === 'paid') bg-emerald-100 text-emerald-800 @elseif($payment->statut === 'pending') bg-amber-100 text-amber-800 @else bg-rose-100 text-rose-800 @endif">{{ ucfirst($payment->statut) }}</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ number_format($payment->montant) }} {{ $payment->devise }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $payment->created_at->diffForHumans() }}</p>
                </div>
                @empty
                <div class="p-4 text-center text-sm text-slate-500">Aucun paiement</div>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4"><h3 class="font-extrabold text-slate-900">Dernières demandes</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse($dernieresDemandes as $demande)
                <a href="{{ route('admin.demandes.show', $demande) }}" class="block p-4 transition hover:bg-slate-50">
                    <div class="flex items-start justify-between gap-2">
                        <p class="text-sm font-semibold text-slate-900">{{ Str::limit($demande->titre, 20) }}</p>
                        <span class="rounded-full px-2 py-1 text-xs font-semibold @if($demande->statut === 'pendante') bg-amber-100 text-amber-800 @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800 @elseif($demande->statut === 'acceptee') bg-emerald-100 text-emerald-800 @else bg-rose-100 text-rose-800 @endif">{{ ucfirst($demande->statut) }}</span>
                    </div>
                    <p class="text-xs text-slate-500">Par: {{ $demande->citoyen->nom }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $demande->created_at->diffForHumans() }}</p>
                </a>
                @empty
                <div class="p-4 text-center text-sm text-slate-500">Aucune demande</div>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-4"><h3 class="font-extrabold text-slate-900">Messages récents</h3></div>
            <div class="divide-y divide-slate-100">
                @forelse($derniersMessages as $message)
                <a href="{{ route('admin.demandes.show', $message->demande) }}" class="block p-4 transition hover:bg-slate-50">
                    <p class="text-sm font-semibold text-slate-900">{{ Str::limit($message->contenu, 60) }}</p>
                    <p class="mt-1 text-xs text-slate-500">De: {{ $message->expediteur->nom ?? $message->expediteur->name ?? 'Utilisateur' }}</p>
                    <p class="text-xs text-slate-400">{{ $message->created_at->diffForHumans() }}</p>
                </a>
                @empty
                <div class="p-4 text-center text-sm text-slate-500">Aucun message</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="mb-4 text-lg font-extrabold text-slate-900">Actions rapides</h2>
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <a href="{{ route('admin.agents.index') }}" class="rounded-2xl border border-slate-200 p-4 text-center transition hover:-translate-y-0.5 hover:border-blue-300 hover:bg-blue-50">
                <p class="text-xl">👥</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Gérer agents</p>
            </a>
            <a href="{{ route('admin.pointage.index') }}" class="rounded-2xl border border-slate-200 p-4 text-center transition hover:-translate-y-0.5 hover:border-emerald-300 hover:bg-emerald-50">
                <p class="text-xl">⏰</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Pointage</p>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="rounded-2xl border border-slate-200 p-4 text-center transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50">
                <p class="text-xl">⚙️</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Paramètres</p>
            </a>
            <a href="{{ route('admin.demandes.index') }}" class="rounded-2xl border border-slate-200 p-4 text-center transition hover:-translate-y-0.5 hover:border-amber-300 hover:bg-amber-50">
                <p class="text-xl">📋</p>
                <p class="mt-2 text-sm font-semibold text-slate-900">Demandes</p>
            </a>
        </div>
    </section>
</div>
@endsection
