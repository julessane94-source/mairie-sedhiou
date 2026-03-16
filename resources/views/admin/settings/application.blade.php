@extends('layouts.app')

@section('title', 'Paramètres - Application')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">🏢 Paramètres de l'application</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.settings.application.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Nom de l'application *</label>
                <input type="text" name="app_name" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required value="{{ \App\Models\PlatformSettings::get('app_name', 'MAIRI') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea name="app_description" class="w-full px-4 py-2 border border-gray-300 rounded-lg" rows="4">{{ \App\Models\PlatformSettings::get('app_description', '') }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Logo</label>
                <input type="file" name="app_logo" class="w-full px-4 py-2 border border-gray-300 rounded-lg" accept="image/*">
                @php
                $logo = \App\Models\PlatformSettings::get('app_logo');
                @endphp
                @if($logo)
                <img src="{{ asset('storage/' . $logo) }}" alt="Logo" class="mt-4 h-32 object-contain">
                @endif
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email de contact *</label>
                <input type="email" name="app_email" class="w-full px-4 py-2 border border-gray-300 rounded-lg" required value="{{ \App\Models\PlatformSettings::get('app_email', 'contact@mairi.sn') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Téléphone</label>
                <input type="tel" name="app_phone" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \App\Models\PlatformSettings::get('app_phone', '') }}">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Adresse</label>
                <input type="text" name="app_address" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \App\Models\PlatformSettings::get('app_address', '') }}">
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
