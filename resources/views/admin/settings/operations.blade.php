@extends('layouts.app')

@section('title', 'Paramètres - Opérations')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">⚙️ Paramètres d'opération</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.settings.operations.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Gestion des demandes</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre maximum de demandes par agent</label>
                    <input type="number" name="max_demandes_par_agent" class="w-full px-4 py-2 border border-gray-300 rounded-lg" min="1" value="{{ \App\Models\PlatformSettings::get('max_demandes_par_agent', 10) }}">
                    <p class="text-xs text-gray-500 mt-1">Au-delà, l'agent sera marqué "surchargé"</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Délai de réponse attendu (en jours)</label>
                    <input type="number" name="delai_reponse_jours" class="w-full px-4 py-2 border border-gray-300 rounded-lg" min="1" value="{{ \App\Models\PlatformSettings::get('delai_reponse_jours', 7) }}">
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Paramètres financiers</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Devise par défaut</label>
                    <select name="devise_par_defaut" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="XOF" {{ \App\Models\PlatformSettings::get('devise_par_defaut', 'XOF') === 'XOF' ? 'selected' : '' }}>XOF - Franc CFA</option>
                        <option value="USD" {{ \App\Models\PlatformSettings::get('devise_par_defaut', 'XOF') === 'USD' ? 'selected' : '' }}>USD - Dollar</option>
                        <option value="EUR" {{ \App\Models\PlatformSettings::get('devise_par_defaut', 'XOF') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Taux de change (à mettre à jour quotidiennement)</label>
                    <input type="number" name="taux_change_usd" class="w-full px-4 py-2 border border-gray-300 rounded-lg" step="0.01" placeholder="ex: 605.5" value="{{ \App\Models\PlatformSettings::get('taux_change_usd', 605.5) }}">
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Horaires de travail</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Heure d'arrivée (HH:MM)</label>
                    <input type="time" name="heure_arrivee" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \App\Models\PlatformSettings::get('heure_arrivee', '08:00') }}">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Heure de départ (HH:MM)</label>
                    <input type="time" name="heure_depart" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \App\Models\PlatformSettings::get('heure_depart', '17:00') }}">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Heures de travail par jour</label>
                    <input type="number" name="heures_travail_par_jour" class="w-full px-4 py-2 border border-gray-300 rounded-lg" step="0.5" min="1" value="{{ \App\Models\PlatformSettings::get('heures_travail_par_jour', 8) }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jour de repos hebdomadaire</label>
                    <select name="jour_repos_hebdo" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        @php $jourRepos = \App\Models\PlatformSettings::get('jour_repos_hebdo', 'dimanche'); @endphp
                        <option value="lundi" {{ $jourRepos === 'lundi' ? 'selected' : '' }}>Lundi</option>
                        <option value="mardi" {{ $jourRepos === 'mardi' ? 'selected' : '' }}>Mardi</option>
                        <option value="mercredi" {{ $jourRepos === 'mercredi' ? 'selected' : '' }}>Mercredi</option>
                        <option value="jeudi" {{ $jourRepos === 'jeudi' ? 'selected' : '' }}>Jeudi</option>
                        <option value="vendredi" {{ $jourRepos === 'vendredi' ? 'selected' : '' }}>Vendredi</option>
                        <option value="samedi" {{ $jourRepos === 'samedi' ? 'selected' : '' }}>Samedi</option>
                        <option value="dimanche" {{ $jourRepos === 'dimanche' ? 'selected' : '' }}>Dimanche</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-200">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    💾 Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
