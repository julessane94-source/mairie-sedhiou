@extends('layouts.app')

@section('title', 'Messages - Agent - Mairi')

@section('content')
<div class="space-y-6">
    <section class="rounded-3xl border border-cyan-200 bg-gradient-to-r from-cyan-700 via-blue-700 to-indigo-700 p-8 text-white shadow-xl">
        <h1 class="text-3xl font-black tracking-tight">Messagerie agent</h1>
        <p class="mt-2 text-sm text-cyan-100">Consultez vos messages recus et envoyes sur les demandes assignees.</p>
    </section>

    <section class="grid grid-cols-1 gap-6 xl:grid-cols-2">
        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5"><h2 class="text-lg font-extrabold text-slate-900">Recus</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($messagesRecus as $message)
                <a href="{{ route('agent.demandes.show', $message->demande) }}" class="block p-5 transition hover:bg-slate-50">
                    <p class="text-sm font-semibold text-slate-900">{{ Str::limit($message->contenu, 120) }}</p>
                    <p class="mt-1 text-xs text-slate-500">De: {{ $message->expediteur->name ?? 'Utilisateur' }}</p>
                    <p class="text-xs text-slate-400">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                </a>
                @empty
                <div class="p-5 text-sm text-slate-500">Aucun message recu.</div>
                @endforelse
            </div>
            <div class="border-t border-slate-200 p-5">{{ $messagesRecus->links() }}</div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-5"><h2 class="text-lg font-extrabold text-slate-900">Envoyes</h2></div>
            <div class="divide-y divide-slate-100">
                @forelse($messagesEnvoyes as $message)
                <a href="{{ route('agent.demandes.show', $message->demande) }}" class="block p-5 transition hover:bg-slate-50">
                    <p class="text-sm font-semibold text-slate-900">{{ Str::limit($message->contenu, 120) }}</p>
                    <p class="mt-1 text-xs text-slate-500">Demande: {{ Str::limit($message->demande->titre, 45) }}</p>
                    <p class="text-xs text-slate-400">{{ $message->created_at->format('d/m/Y H:i') }}</p>
                </a>
                @empty
                <div class="p-5 text-sm text-slate-500">Aucun message envoye.</div>
                @endforelse
            </div>
            <div class="border-t border-slate-200 p-5">{{ $messagesEnvoyes->links() }}</div>
        </article>
    </section>
</div>
@endsection
