@extends('layouts.app')

@section('title', 'Page d\'accueil')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('admin.settings.index') }}" class="text-blue-600 hover:underline">← Retour</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">🏠 Paramètres de la Page d'Accueil</h1>
    </div>

    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('admin.settings.homepage.update') }}" method="POST" class="space-y-6 p-6">
            @csrf

            <!-- Section: Horaires -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">⏰ Horaires d'Ouverture</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Lundi - Vendredi</label>
                        <input type="text" name="hours_weekday" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="8h00 - 18h00" value="{{ \App\Models\PlatformSettings::get('hours_weekday', '8h00 - 18h00') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Samedi</label>
                        <input type="text" name="hours_saturday" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="9h00 - 14h00" value="{{ \App\Models\PlatformSettings::get('hours_saturday', '9h00 - 14h00') }}">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dimanche</label>
                    <input type="text" name="hours_sunday" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        placeholder="Fermé" value="{{ \App\Models\PlatformSettings::get('hours_sunday', 'Fermé') }}">
                </div>
            </div>

            <!-- Section: Réseaux Sociaux -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">📱 Réseaux Sociaux</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-facebook"></i> Facebook
                        </label>
                        <input type="url" name="social_facebook" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="https://www.facebook.com/mairi.dakar" 
                            value="{{ \App\Models\PlatformSettings::get('social_facebook', 'https://www.facebook.com/mairi.dakar') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-twitter"></i> Twitter
                        </label>
                        <input type="url" name="social_twitter" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="https://www.twitter.com/mairi_dakar" 
                            value="{{ \App\Models\PlatformSettings::get('social_twitter', 'https://www.twitter.com/mairi_dakar') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-instagram"></i> Instagram
                        </label>
                        <input type="url" name="social_instagram" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="https://www.instagram.com/mairi_dakar" 
                            value="{{ \App\Models\PlatformSettings::get('social_instagram', 'https://www.instagram.com/mairi_dakar') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-linkedin"></i> LinkedIn
                        </label>
                        <input type="url" name="social_linkedin" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="https://www.linkedin.com/company/mairi-dakar" 
                            value="{{ \App\Models\PlatformSettings::get('social_linkedin', 'https://www.linkedin.com/company/mairi-dakar') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-youtube"></i> YouTube
                        </label>
                        <input type="url" name="social_youtube" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="https://www.youtube.com/@mairi_dakar" 
                            value="{{ \App\Models\PlatformSettings::get('social_youtube', 'https://www.youtube.com/@mairi_dakar') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </label>
                        <input type="url" name="social_whatsapp" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="https://wa.me/221XXXXXXXXX" 
                            value="{{ \App\Models\PlatformSettings::get('social_whatsapp', '') }}">
                    </div>
                </div>
            </div>

            <!-- Section: Statistiques Accueil -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">📊 Statistiques Affichées</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Citoyens Actifs</label>
                        <input type="text" name="stat_citizens" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="50,000+" value="{{ \App\Models\PlatformSettings::get('stat_citizens', '50,000+') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Demandes Traitées</label>
                        <input type="text" name="stat_requests" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="100,000+" value="{{ \App\Models\PlatformSettings::get('stat_requests', '100,000+') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Disponibilité</label>
                        <input type="text" name="stat_availability" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                            placeholder="24/7" value="{{ \App\Models\PlatformSettings::get('stat_availability', '24/7') }}">
                    </div>
                </div>
            </div>

            <!-- Section: Messages -->
            <div class="border-t pt-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">✍️ Textes de la Page</h2>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Sous-titre du Hero</label>
                    <input type="text" name="hero_subtitle" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        placeholder="Plateforme numérique des services municipaux" 
                        value="{{ \App\Models\PlatformSettings::get('hero_subtitle', 'Plateforme numérique des services municipaux') }}">
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Description Hero</label>
                    <textarea name="hero_description" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ \App\Models\PlatformSettings::get('hero_description', 'Accédez facilement aux services de votre mairie, soumettez vos demandes et suivez votre dossier en ligne') }}</textarea>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Slogan "À Propos"</label>
                    <input type="text" name="about_slogan" class="w-full px-4 py-2 border border-gray-300 rounded-lg" 
                        placeholder="Transformant les services municipaux pour le bien de tous" 
                        value="{{ \App\Models\PlatformSettings::get('about_slogan', 'Transformant les services municipaux pour le bien de tous') }}">
                </div>
            </div>

            <div class="border-t pt-6">
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold">
                    💾 Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <!-- Info Box -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-900 mb-2">💡 Information</h3>
        <p class="text-blue-800">
            Les modifications apportées ici s'afficheront automatiquement sur la page d'accueil du site.
        </p>
    </div>
</div>
@endsection
