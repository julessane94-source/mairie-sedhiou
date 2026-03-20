@extends('layouts.app')

@section('title', 'Nouvelle demande - Mairi')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <section class="relative overflow-hidden rounded-3xl border border-sky-200 bg-gradient-to-r from-sky-700 via-blue-700 to-cyan-600 p-8 text-white shadow-xl">
        <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-cyan-200/25 blur-3xl"></div>
        <div class="absolute -left-10 -bottom-10 h-44 w-44 rounded-full bg-blue-200/20 blur-3xl"></div>
        <div class="relative">
            <p class="inline-block rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em]">Dépôt citoyen</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight">Créer une nouvelle demande</h1>
            <p class="mt-2 max-w-2xl text-sm text-blue-100">Choisissez un service actif de la mairie et décrivez votre besoin. Les délais et frais estimés s'affichent automatiquement.</p>
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <form action="{{ route('citoyen.demandes.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Titre de la demande</label>
                <input
                    type="text"
                    name="titre"
                    value="{{ old('titre') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('titre') border-rose-500 @enderror"
                    placeholder="Ex: Demande de certificat de résidence"
                >
                @error('titre')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Type de demande</label>
                <select
                    name="type"
                    id="type_demande"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('type') border-rose-500 @enderror"
                >
                    <option value="">Sélectionnez un service</option>
                    @foreach($typesDemandes as $categorie => $types)
                        <optgroup label="{{ $categorie }}">
                            @foreach($types as $type)
                                <option value="{{ $type['value'] }}" data-delai="{{ $type['delai'] }}" data-frais="{{ $type['frais'] }}" {{ old('type') === $type['value'] ? 'selected' : '' }}>
                                    {{ $type['label'] }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror

                <div id="type-info" class="mt-4 hidden rounded-2xl border border-blue-200 bg-blue-50 p-4">
                    <div class="grid grid-cols-1 gap-3 text-sm text-blue-900 sm:grid-cols-2">
                        <div class="rounded-xl bg-white px-3 py-2">
                            <span class="font-semibold">Délai estimé:</span>
                            <span id="delai-info"></span>
                        </div>
                        <div class="rounded-xl bg-white px-3 py-2">
                            <span class="font-semibold">Frais:</span>
                            <span id="frais-info"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Priorité</label>
                <select name="priorite" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    <option value="normale" {{ old('priorite', 'normale') === 'normale' ? 'selected' : '' }}>Normale</option>
                    <option value="basse" {{ old('priorite') === 'basse' ? 'selected' : '' }}>Basse</option>
                    <option value="haute" {{ old('priorite') === 'haute' ? 'selected' : '' }}>Haute</option>
                    <option value="urgente" {{ old('priorite') === 'urgente' ? 'selected' : '' }}>Urgente</option>
                </select>
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-slate-700">Description</label>
                <textarea
                    name="description"
                    rows="8"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-800 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('description') border-rose-500 @enderror"
                    placeholder="Décrivez votre demande avec le plus de détails possible"
                >{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-blue-600 to-cyan-600 px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:brightness-110">
                    Soumettre la demande
                </button>
                <a href="{{ route('citoyen.demandes.index') }}" class="rounded-xl border border-slate-300 bg-slate-100 px-6 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                    Annuler
                </a>
            </div>
        </form>
    </section>
</div>

<script>
(function () {
    const select = document.getElementById('type_demande');
    const typeInfo = document.getElementById('type-info');
    const delaiInfo = document.getElementById('delai-info');
    const fraisInfo = document.getElementById('frais-info');

    function updateTypeInfo() {
        const selectedOption = select.options[select.selectedIndex];
        const delai = selectedOption.getAttribute('data-delai');
        const frais = selectedOption.getAttribute('data-frais');

        if (delai && frais) {
            delaiInfo.textContent = delai + ' jours';
            fraisInfo.textContent = Number(frais).toLocaleString('fr-FR') + ' FCFA';
            typeInfo.classList.remove('hidden');
        } else {
            typeInfo.classList.add('hidden');
        }
    }

    select.addEventListener('change', updateTypeInfo);
    updateTypeInfo();
})();
</script>
@endsection
