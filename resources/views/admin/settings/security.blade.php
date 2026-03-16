@extends('layouts.app')

@section('title', 'Paramètres - Sécurité')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">🔐 Paramètres de sécurité</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.settings.security.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Authentification</h2>

                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="enable_2fa" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('enable_2fa', false) ? 'checked' : '' }}>
                        <span class="ml-3 font-semibold text-gray-900">Activer l'authentification à deux facteurs (2FA)</span>
                    </label>
                    <p class="text-xs text-gray-600 mt-2 ml-7">Les utilisateurs devront confirmer leur identité avec un code SMS ou une application</p>
                </div>

                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="require_https" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('require_https', true) ? 'checked' : '' }}>
                        <span class="ml-3 font-semibold text-gray-900">Forcer HTTPS</span>
                    </label>
                    <p class="text-xs text-gray-600 mt-2 ml-7">Toutes les connexions seront chiffrées</p>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Sessions</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Délai d'inactivité avant déconnexion (en minutes)</label>
                    <input type="number" name="session_timeout" class="w-full px-4 py-2 border border-gray-300 rounded-lg" min="5" step="5" value="{{ \App\Models\PlatformSettings::get('session_timeout', 60) }}">
                    <p class="text-xs text-gray-500 mt-1">Les utilisateurs inactifs seront automatiquement déconnectés</p>
                </div>

                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="disable_concurrent_sessions" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('disable_concurrent_sessions', false) ? 'checked' : '' }}>
                        <span class="ml-3 font-semibold text-gray-900">Empêcher les sessions concurrentes</span>
                    </label>
                    <p class="text-xs text-gray-600 mt-2 ml-7">Un utilisateur ne peut être connecté qu'une seule fois à la fois</p>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Tentatives de connexion</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nombre maximum de tentatives échouées</label>
                    <input type="number" name="login_max_attempts" class="w-full px-4 py-2 border border-gray-300 rounded-lg" min="1" value="{{ \App\Models\PlatformSettings::get('login_max_attempts', 5) }}">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Durée de blocage après dépassement (en minutes)</label>
                    <input type="number" name="login_lockout_duration" class="w-full px-4 py-2 border border-gray-300 rounded-lg" min="1" value="{{ \App\Models\PlatformSettings::get('login_lockout_duration', 15) }}">
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Mots de passe</h2>

                <div class="mb-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <p class="text-sm font-semibold text-blue-900">Configuration minimale recommandée</p>
                    <ul class="text-xs text-blue-800 mt-2 space-y-1 ml-4">
                        <li>✓ Longueur minimale: 8 caractères</li>
                        <li>✓ Doit contenir une majuscule (A-Z)</li>
                        <li>✓ Doit contenir une minuscule (a-z)</li>
                        <li>✓ Doit contenir un chiffre (0-9)</li>
                        <li>✓ Doit contenir un caractère spécial (!@#$%...)</li>
                    </ul>
                </div>

                <div class="mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="force_password_change_first_login" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('force_password_change_first_login', true) ? 'checked' : '' }}>
                        <span class="ml-3 font-semibold text-gray-900">Forcer le changement de mot de passe à la première connexion</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Renouvellement du mot de passe (en jours, 0=jamais)</label>
                    <input type="number" name="password_renewal_days" class="w-full px-4 py-2 border border-gray-300 rounded-lg" min="0" value="{{ \App\Models\PlatformSettings::get('password_renewal_days', 90) }}">
                    <p class="text-xs text-gray-500 mt-1">Les utilisateurs devront changer leur mot de passe après ce délai</p>
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
