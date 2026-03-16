@extends('layouts.app')

@section('title', 'Paramètres - Notifications')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">📧 Paramètres de notification</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('admin.settings.notifications.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📧 Notifications par email</h2>

                <div class="space-y-3">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_demande_new" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_email_demande_new', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Nouvelle demande</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-2 ml-7">Notifier quand une nouvelle demande est créée</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_demande_assign" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_email_demande_assign', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Demande assignée à agent</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-2 ml-7">Notifier l'agent quand une demande lui est assignée</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_demande_status" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_email_demande_status', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Changement de statut de demande</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-2 ml-7">Notifier le demandeur de tout changement de statut</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_payment" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_email_payment', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Confirmation de paiement</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-2 ml-7">Notifier quand un paiement est confirmé</p>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_email_message" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_email_message', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Nouveau message</span>
                        </label>
                        <p class="text-xs text-gray-600 mt-2 ml-7">Notifier de l'arrivée d'un nouveau message</p>
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">📱 Notifications par SMS</h2>

                <div class="mb-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                    <p class="text-sm font-semibold text-yellow-900">⚠️ Attention</p>
                    <p class="text-xs text-yellow-800 mt-1">Nécessite une configuration du prestataire SMS</p>
                </div>

                <div class="space-y-3">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_sms_demande_assign" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_sms_demande_assign', false) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Demande assignée à agent</span>
                        </label>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_sms_payment" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_sms_payment', false) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Confirmation de paiement</span>
                        </label>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_sms_otp" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_sms_otp', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Codes OTP (authentification)</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prestataire SMS</label>
                        <select name="sms_provider" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            @php $provider = \App\Models\PlatformSettings::get('sms_provider', 'twilio'); @endphp
                            <option value="twilio" {{ $provider === 'twilio' ? 'selected' : '' }}>Twilio</option>
                            <option value="aws_sns" {{ $provider === 'aws_sns' ? 'selected' : '' }}>AWS SNS</option>
                            <option value="local" {{ $provider === 'local' ? 'selected' : '' }}>Prestataire local</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">🔔 Notifications in-app</h2>

                <div class="space-y-3">
                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_inapp_demande" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_inapp_demande', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Changements de demande</span>
                        </label>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_inapp_message" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_inapp_message', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Nouveaux messages</span>
                        </label>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="notify_inapp_system" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_inapp_system', true) ? 'checked' : '' }}>
                            <span class="ml-3 font-semibold text-gray-900">Notifications système</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="border-b border-gray-200 pb-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-4">⏰ Paramètres généraux</h2>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Heure de début pour notifications (HH:MM)</label>
                    <input type="time" name="notify_start_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \App\Models\PlatformSettings::get('notify_start_time', '08:00') }}">
                    <p class="text-xs text-gray-500 mt-1">Les notifications ne seront envoyées qu'après cette heure</p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Heure de fin pour notifications (HH:MM)</label>
                    <input type="time" name="notify_end_time" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \App\Models\PlatformSettings::get('notify_end_time', '18:00') }}">
                </div>

                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="notify_disable_weekends" value="1" class="w-4 h-4 text-blue-600 rounded" {{ \App\Models\PlatformSettings::get('notify_disable_weekends', true) ? 'checked' : '' }}>
                        <span class="ml-3 font-semibold text-gray-900">Désactiver les notifications pendant les fins de semaine</span>
                    </label>
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
