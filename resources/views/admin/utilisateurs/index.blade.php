@extends('layouts.app')

@section('title', (($citoyensOnly ?? false) ? 'Citoyens' : 'Utilisateurs') . ' - Admin - Mairi')

@section('content')
@php $isCitoyens = $citoyensOnly ?? false; @endphp
<div class="space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-indigo-200 bg-gradient-to-r from-indigo-700 via-blue-700 to-cyan-700 p-8 text-white shadow-xl">
        <div class="absolute -right-12 -top-12 h-44 w-44 rounded-full bg-cyan-200/20 blur-3xl"></div>
        <h1 class="text-3xl font-black tracking-tight">{{ $isCitoyens ? 'Liste des citoyens' : 'Gestion des utilisateurs' }}</h1>
        <p class="mt-2 text-sm text-indigo-100">Filtrez par statut, role et recherche rapide.</p>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
        <form action="{{ $isCitoyens ? route('admin.citoyens.index') : route('admin.utilisateurs.index') }}" method="GET" class="grid grid-cols-1 gap-3 md:grid-cols-5">
            @if(!$isCitoyens)
            <select name="role" class="rounded-xl border border-slate-300 px-3 py-2">
                <option value="">Tous les roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                <option value="citoyen" @selected(request('role') === 'citoyen')>Citoyen</option>
                <option value="agent" @selected(request('role') === 'agent')>Agent</option>
            </select>
            @endif
            <select name="statut" class="rounded-xl border border-slate-300 px-3 py-2">
                <option value="">Tous les statuts</option>
                <option value="actif" @selected(request('statut') === 'actif')>Actif</option>
                <option value="inactif" @selected(request('statut') === 'inactif')>Inactif</option>
                <option value="suspendu" @selected(request('statut') === 'suspendu')>Suspendu</option>
            </select>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, numero citoyen" class="rounded-xl border border-slate-300 px-3 py-2 md:col-span-2">
            <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-4 py-2 text-sm font-bold text-white">Filtrer</button>
            <a href="{{ $isCitoyens ? route('admin.citoyens.index') : route('admin.utilisateurs.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-center text-sm font-semibold text-slate-700">Reinitialiser</a>
        </form>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wider text-slate-500">
                        <th class="px-4 py-3">Nom</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Statut</th>
                        <th class="px-4 py-3">Numero citoyen</th>
                        <th class="px-4 py-3">Depuis le</th>
                        <th class="px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($utilisateurs as $user)
                    <tr class="border-b border-slate-100">
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold @if($user->role === 'admin') bg-rose-100 text-rose-800 @elseif($user->role === 'agent') bg-emerald-100 text-emerald-800 @else bg-blue-100 text-blue-800 @endif">{{ ucfirst($user->role) }}</span></td>
                        <td class="px-4 py-3"><span class="rounded-full px-2 py-1 text-xs font-semibold @if($user->statut === 'actif') bg-emerald-100 text-emerald-800 @elseif($user->statut === 'inactif') bg-slate-100 text-slate-700 @else bg-rose-100 text-rose-800 @endif">{{ ucfirst($user->statut) }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->numero_citoyen ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->created_at->format('d/m/Y') }}</td>
                        <td class="px-4 py-3"><a href="{{ route('admin.utilisateurs.show', $user) }}" class="font-semibold text-blue-700 hover:underline">Voir</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-6 text-center text-slate-500">Aucun utilisateur trouve.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($utilisateurs->hasPages())
        <div class="border-t border-slate-200 p-5">{{ $utilisateurs->links() }}</div>
        @endif
    </section>
</div>
@endsection
