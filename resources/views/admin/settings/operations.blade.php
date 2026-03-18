@extends('layouts.app')

@section('title', 'Paramètres - Opérations')

@section('content')
<div class="mx-auto max-w-5xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('admin.settings.index') }}" class="text-sm font-semibold text-blue-700 hover:underline">Retour aux paramètres</a>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">Paramètres d'opération</h1>
            <p class="mt-1 text-sm text-slate-500">Configurez les règles de traitement et les services que la mairie met à disposition.</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-600">
            Impact immédiat sur le formulaire citoyen
        </div>
    </div>

    <form action="{{ route('admin.settings.operations.update') }}" method="POST" class="space-y-6">
        @csrf

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-slate-900">Gestion des demandes</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Nombre maximum de demandes par agent</label>
                    <input type="number" name="max_demandes_par_agent" class="w-full rounded-xl border border-slate-300 px-4 py-2" min="1" value="{{ old('max_demandes_par_agent', \App\Models\PlatformSettings::get('max_demandes_par_agent', 10)) }}">
                    @error('max_demandes_par_agent')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Délai de réponse attendu (jours)</label>
                    <input type="number" name="delai_reponse_jours" class="w-full rounded-xl border border-slate-300 px-4 py-2" min="1" value="{{ old('delai_reponse_jours', \App\Models\PlatformSettings::get('delai_reponse_jours', 7)) }}">
                    @error('delai_reponse_jours')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-slate-900">Services actifs pour les citoyens</h2>
            <p class="mt-1 text-sm text-slate-500">Activez ou désactivez les services proposés dans le formulaire de dépôt citoyen.</p>

            @foreach($municipalServices as $categorie => $services)
            <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-slate-700">{{ $categorie }}</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach($services as $service)
                    <label class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2">
                        <span class="text-sm font-medium text-slate-700">{{ $service['label'] }}</span>
                        <input
                            type="checkbox"
                            name="services_actifs[]"
                            value="{{ $service['value'] }}"
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            {{ in_array($service['value'], old('services_actifs', $servicesActifs), true) ? 'checked' : '' }}
                        >
                    </label>
                    @endforeach
                </div>
            </div>
            @endforeach
            @error('services_actifs')<p class="mt-3 text-xs text-rose-600">{{ $message }}</p>@enderror
            @error('services_actifs.*')<p class="mt-3 text-xs text-rose-600">{{ $message }}</p>@enderror
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-slate-900">Paramètres financiers</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Devise par défaut</label>
                    @php $devise = old('devise_par_defaut', \App\Models\PlatformSettings::get('devise_par_defaut', 'XOF')); @endphp
                    <select name="devise_par_defaut" class="w-full rounded-xl border border-slate-300 px-4 py-2">
                        <option value="XOF" {{ $devise === 'XOF' ? 'selected' : '' }}>XOF - Franc CFA</option>
                        <option value="USD" {{ $devise === 'USD' ? 'selected' : '' }}>USD - Dollar</option>
                        <option value="EUR" {{ $devise === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    </select>
                    @error('devise_par_defaut')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Heures de travail par jour</label>
                    <input type="number" name="heures_travail_par_jour" class="w-full rounded-xl border border-slate-300 px-4 py-2" step="0.5" min="1" value="{{ old('heures_travail_par_jour', \App\Models\PlatformSettings::get('heures_travail_par_jour', 8)) }}">
                    @error('heures_travail_par_jour')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-extrabold text-slate-900">Organisation hebdomadaire</h2>
            <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-slate-700">Jour de repos hebdomadaire</label>
                    @php $jourRepos = old('jour_repos_hebdo', \App\Models\PlatformSettings::get('jour_repos_hebdo', 'dimanche')); @endphp
                    <select name="jour_repos_hebdo" class="w-full rounded-xl border border-slate-300 px-4 py-2">
                        <option value="lundi" {{ $jourRepos === 'lundi' ? 'selected' : '' }}>Lundi</option>
                        <option value="mardi" {{ $jourRepos === 'mardi' ? 'selected' : '' }}>Mardi</option>
                        <option value="mercredi" {{ $jourRepos === 'mercredi' ? 'selected' : '' }}>Mercredi</option>
                        <option value="jeudi" {{ $jourRepos === 'jeudi' ? 'selected' : '' }}>Jeudi</option>
                        <option value="vendredi" {{ $jourRepos === 'vendredi' ? 'selected' : '' }}>Vendredi</option>
                        <option value="samedi" {{ $jourRepos === 'samedi' ? 'selected' : '' }}>Samedi</option>
                        <option value="dimanche" {{ $jourRepos === 'dimanche' ? 'selected' : '' }}>Dimanche</option>
                    </select>
                    @error('jour_repos_hebdo')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end">
                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700">
                        <input type="checkbox" name="activer_paiements_en_ligne" value="1" class="h-4 w-4 rounded border-slate-300 text-blue-600" {{ \App\Models\PlatformSettings::get('activer_paiements_en_ligne', false) ? 'checked' : '' }}>
                        Activer les paiements en ligne
                    </label>
                </div>
            </div>
        </section>

        <div class="flex justify-end">
            <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:brightness-110">
                Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>
@endsection
