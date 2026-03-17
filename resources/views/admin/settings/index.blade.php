@extends('layouts.app')

@section('title', 'Paramètres de la plateforme')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">⚙️ Paramètres de la plateforme</h1>
    <p class="text-gray-600 mt-1">Configurez les informations et les paramètres systèmes</p>
</div>

<!-- Menu de navigation -->
<div class="bg-white rounded-lg shadow mb-6 overflow-hidden">
    <div class="flex flex-wrap border-b border-gray-200">
        <a href="{{ route('admin.settings.application') }}" class="flex-1 px-4 py-3 text-center font-semibold text-blue-600 border-b-2 border-blue-600 min-w-fit">
            🏢 Application
        </a>
        <a href="{{ route('admin.settings.homepage') }}" class="flex-1 px-4 py-3 text-center font-semibold text-gray-600 hover:text-gray-900 min-w-fit">
            🏠 Accueil
        </a>
        <a href="{{ route('admin.settings.operations') }}" class="flex-1 px-4 py-3 text-center font-semibold text-gray-600 hover:text-gray-900 min-w-fit">
            📊 Opérations
        </a>
        <a href="{{ route('admin.settings.security') }}" class="flex-1 px-4 py-3 text-center font-semibold text-gray-600 hover:text-gray-900 min-w-fit">
            🔒 Sécurité
        </a>
        <a href="{{ route('admin.settings.notifications') }}" class="flex-1 px-4 py-3 text-center font-semibold text-gray-600 hover:text-gray-900 min-w-fit">
            🔔 Notifications
        </a>
        <a href="{{ route('admin.settings.logs') }}" class="flex-1 px-4 py-3 text-center font-semibold text-gray-600 hover:text-gray-900 min-w-fit">
            📋 Logs
        </a>
    </div>
</div>

<!-- Paramètres -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    @foreach($categories as $category => $categorySettings)
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ $category }}</h3>
        <div class="space-y-4">
            @forelse($categorySettings as $key => $value)
            <div class="border-b pb-3">
                <label class="text-sm text-gray-600">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                @php
                $setting = $settings[$key] ?? null;
                @endphp
                
                <form action="{{ route('admin.settings.update', $key) }}" method="POST" class="flex gap-2 mt-2">
                    @csrf
                    @method('PATCH')
                    <input type="text" name="valeur" class="flex-1 px-3 py-1 border border-gray-300 rounded text-sm" value="{{ $value }}" @if($setting && !$setting->isModifiable()) disabled @endif>
                    @if(!$setting || $setting->isModifiable())
                    <button type="submit" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700">Save</button>
                    @endif
                </form>
            </div>
            @empty
            <p class="text-gray-500 text-sm">Aucun paramètre dans cette catégorie</p>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

<!-- Actions supplémentaires -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">📦 Sauvegarde de données</h3>
        <form action="{{ route('admin.settings.backup') }}" method="POST">
            @csrf
            <p class="text-gray-600 text-sm mb-4">Créer une copie de sauvegarde de la base de données.</p>
            <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-semibold">
                💾 Effectuer une sauvegarde
            </button>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">🧹 Nettoyage</h3>
        <form action="{{ route('admin.settings.logs.clear') }}" method="POST" onsubmit="return confirm('Êtes-vous sûr ?');">
            @csrf
            <p class="text-gray-600 text-sm mb-4">Effacer tous les logs du système.</p>
            <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold">
                🗑️ Effacer les logs
            </button>
        </form>
    </div>
</div>

<!-- Messages de succès -->
@if(session('success'))
<div class="mt-6 p-4 bg-green-100 border border-green-400 text-green-800 rounded-lg">
    ✅ {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="mt-6 p-4 bg-red-100 border border-red-400 text-red-800 rounded-lg">
    ❌ {{ session('error') }}
</div>
@endif
@endsection
