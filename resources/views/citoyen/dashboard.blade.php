@extends('layouts.app')

@section('title', 'Dashboard Citoyen - Mairi')

@section('content')
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-sky-200 bg-gradient-to-r from-sky-700 via-blue-700 to-cyan-600 p-8 text-white shadow-xl">
        <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-cyan-200/20 blur-3xl"></div>
        <h1 class="text-3xl font-black tracking-tight">Mon Dashboard Citoyen</h1>
        <p class="mt-2 text-sm text-blue-100">Suivez vos demandes, vos echanges recus et vos messages envoyes.</p>
    </section>

    <section class="grid grid-cols-1 gap-4 md:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase tracking-wider text-slate-500">Demandes totales</p><p class="mt-2 text-3xl font-black text-blue-700">{{ $demandes->total() }}</p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase tracking-wider text-slate-500">Pendantes</p><p class="mt-2 text-3xl font-black text-amber-600">{{ $demandesPendantes }}</p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase tracking-wider text-slate-500">Acceptees</p><p class="mt-2 text-3xl font-black text-emerald-600">{{ $demandesAcceptees }}</p></article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"><p class="text-xs uppercase tracking-wider text-slate-500">Rejetees</p><p class="mt-2 text-3xl font-black text-rose-600">{{ $demandesRejetees }}</p></article>
    </section>

    <div>
        <a href="{{ route('citoyen.demandes.create') }}" class="inline-block rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:brightness-110">Nouvelle demande</a>
    </div>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5"><h2 class="text-lg font-extrabold text-slate-900">Messages recus</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($messagesRecus as $message)
                <a href="{{ route('citoyen.demandes.show', $message->demande) }}" class="block p-5 transition hover:bg-slate-50">
                    <p class="text-sm font-semibold text-slate-900">{{ Str::limit($message->contenu, 110) }}</p>
                    <p class="mt-1 text-xs text-slate-500">De: {{ $message->expediteur->name ?? 'Utilisateur' }}</p>
                    <p class="text-xs text-slate-400">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                </a>
                @empty
                <div class="p-5 text-sm text-slate-500">Aucun message recu.</div>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5"><h2 class="text-lg font-extrabold text-slate-900">Messages envoyes</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($messagesEnvoyes as $message)
                <a href="{{ route('citoyen.demandes.show', $message->demande) }}" class="block p-5 transition hover:bg-slate-50">
                    <p class="text-sm font-semibold text-slate-900">{{ Str::limit($message->contenu, 110) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Demande: {{ Str::limit($message->demande->titre, 45) }}</p>
                    <p class="text-xs text-slate-400">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                </a>
                @empty
                <div class="p-5 text-sm text-slate-500">Aucun message envoye.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-5"><h2 class="text-lg font-extrabold text-slate-900">Mes demandes</h2></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3">Titre</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Priorite</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($demandes as $demande)
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $demande->titre }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $demande->type }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold @if($demande->statut === 'pendante') bg-amber-100 text-amber-800 @elseif($demande->statut === 'en_cours') bg-blue-100 text-blue-800 @elseif($demande->statut === 'acceptee') bg-emerald-100 text-emerald-800 @else bg-rose-100 text-rose-800 @endif">{{ ucfirst($demande->statut) }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ ucfirst($demande->priorite) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $demande->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3"><a href="{{ route('citoyen.demandes.show', $demande) }}" class="font-semibold text-blue-700 hover:underline">Voir</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-5 text-center text-slate-500">Aucune demande disponible.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($demandes->hasPages())
        <div class="border-t border-slate-200 p-5">{{ $demandes->links() }}</div>
        @endif
    </section>
</div>
@endsection
