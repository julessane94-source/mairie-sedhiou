@extends('layouts.app')

@section('title', 'Mon profil - Mairi')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-cyan-200 bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 p-8 text-white shadow-xl">
        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-200/20 blur-3xl"></div>
        <div class="absolute -left-12 -bottom-12 h-40 w-40 rounded-full bg-blue-200/15 blur-3xl"></div>
        <div class="relative">
            <p class="inline-block rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">Compte</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight">Mon profil</h1>
            <p class="mt-2 text-sm text-blue-100">Mettez a jour vos informations de contact et vos donnees d'identite.</p>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-8">
            @csrf
            @method('PATCH')

            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Informations de compte</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Nom complet</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('name') border-rose-500 @enderror">
                        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('email') border-rose-500 @enderror">
                        @error('email')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Prenom</label>
                        <input type="text" name="prenom" value="{{ old('prenom', $user->prenom) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('prenom') border-rose-500 @enderror">
                        @error('prenom')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Nom</label>
                        <input type="text" name="nom" value="{{ old('nom', $user->nom) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('nom') border-rose-500 @enderror">
                        @error('nom')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <div>
                <h2 class="text-lg font-extrabold text-slate-900">Profil personnel</h2>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Telephone</label>
                        <input type="text" name="telephone" value="{{ old('telephone', $user->profil?->telephone) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('telephone') border-rose-500 @enderror">
                        @error('telephone')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Ville</label>
                        <input type="text" name="ville" value="{{ old('ville', $user->profil?->ville) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('ville') border-rose-500 @enderror">
                        @error('ville')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Adresse</label>
                        <input type="text" name="adresse" value="{{ old('adresse', $user->profil?->adresse) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('adresse') border-rose-500 @enderror">
                        @error('adresse')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Code postal</label>
                        <input type="text" name="code_postal" value="{{ old('code_postal', $user->profil?->code_postal) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('code_postal') border-rose-500 @enderror">
                        @error('code_postal')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Date de naissance</label>
                        <input type="date" name="date_naissance" value="{{ old('date_naissance', $user->profil?->date_naissance?->format('Y-m-d')) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('date_naissance') border-rose-500 @enderror">
                        @error('date_naissance')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Lieu de naissance</label>
                        <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance', $user->profil?->lieu_naissance) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('lieu_naissance') border-rose-500 @enderror">
                        @error('lieu_naissance')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Numero de registre</label>
                        <input type="text" name="numero_registre" value="{{ old('numero_registre', $user->profil?->numero_registre) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('numero_registre') border-rose-500 @enderror">
                        @error('numero_registre')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Numero d'identification</label>
                        <input type="text" name="num_id" value="{{ old('num_id', $user->profil?->num_id) }}" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('num_id') border-rose-500 @enderror">
                        @error('num_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Type d'ID</label>
                        @php $typeId = old('type_id', $user->profil?->type_id); @endphp
                        <select name="type_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('type_id') border-rose-500 @enderror">
                            <option value="">Selectionner</option>
                            <option value="CNI" {{ $typeId === 'CNI' ? 'selected' : '' }}>CNI</option>
                            <option value="Passeport" {{ $typeId === 'Passeport' ? 'selected' : '' }}>Passeport</option>
                            <option value="Permis" {{ $typeId === 'Permis' ? 'selected' : '' }}>Permis</option>
                        </select>
                        @error('type_id')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Bio</label>
                        <textarea name="bio" rows="4" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('bio') border-rose-500 @enderror">{{ old('bio', $user->profil?->bio) }}</textarea>
                        @error('bio')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            @if($user->role === 'citoyen')
            <div class="rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900">
                <p class="font-semibold">Numero citoyen</p>
                <p>{{ $user->numero_citoyen ?? 'Il sera genere automatiquement des que la date de naissance et le numero de registre sont renseignes.' }}</p>
            </div>
            @endif

            <div class="flex justify-end gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:brightness-110">
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
