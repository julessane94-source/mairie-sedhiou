<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MAIRI - Plateforme de Services Municipaux</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .service-card {
            transition: all 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">

    <!-- Navigation Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <i class="fas fa-city text-blue-600 text-2xl"></i>
                <span class="text-2xl font-bold text-gray-900">MAIRI</span>
            </div>
            <div class="flex items-center space-x-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-gray-600 hover:text-blue-600 font-semibold">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-gray-600 hover:text-blue-600 font-semibold">Se connecter</a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 font-semibold transition">
                                S'inscrire
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center fade-in">
            <h1 class="text-5xl sm:text-6xl font-bold mb-6">Bienvenue à MAIRI</h1>
            <p class="text-xl sm:text-2xl mb-8 text-gray-100">{{ \App\Models\PlatformSettings::get('hero_subtitle', 'Plateforme numérique des services municipaux') }}</p>
            <p class="text-lg mb-12 text-gray-200 max-w-2xl mx-auto">
                {{ \App\Models\PlatformSettings::get('hero_description', 'Accédez facilement aux services de votre mairie, soumettez vos demandes et suivez votre dossier en ligne') }}
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                            Accéder à mon espace
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                            Se connecter
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-white hover:text-blue-600 transition">
                                Créer un compte
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Nos Services</h2>
                <p class="text-xl text-gray-600">Découvrez l'ensemble des services disponibles en ligne</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Service 1 -->
                <div class="service-card bg-gradient-to-br from-blue-50 to-blue-100 p-8 rounded-lg border border-blue-200">
                    <div class="text-4xl mb-4 text-blue-600">
                        <i class="fas fa-birthday-cake"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">État Civil</h3>
                    <p class="text-gray-700 mb-4">
                        Déclarations de naissance, mariages, décès, certificats de vie, cartes d'identité, passeports...
                    </p>
                    <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:text-blue-800">En savoir plus →</a>
                </div>

                <!-- Service 2 -->
                <div class="service-card bg-gradient-to-br from-green-50 to-green-100 p-8 rounded-lg border border-green-200">
                    <div class="text-4xl mb-4 text-green-600">
                        <i class="fas fa-building"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Urbanisme & Construction</h3>
                    <p class="text-gray-700 mb-4">
                        Permis de construire, autorisations de travaux, certificats de conformité, plans cadastraux...
                    </p>
                    <a href="{{ route('register') }}" class="text-green-600 font-semibold hover:text-green-800">En savoir plus →</a>
                </div>

                <!-- Service 3 -->
                <div class="service-card bg-gradient-to-br from-purple-50 to-purple-100 p-8 rounded-lg border border-purple-200">
                    <div class="text-4xl mb-4 text-purple-600">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Commerce & Entreprises</h3>
                    <p class="text-gray-700 mb-4">
                        Licences commerciales, patentes, registres du commerce, autorisations d'exploitation...
                    </p>
                    <a href="{{ route('register') }}" class="text-purple-600 font-semibold hover:text-purple-800">En savoir plus →</a>
                </div>

                <!-- Service 4 -->
                <div class="service-card bg-gradient-to-br from-yellow-50 to-yellow-100 p-8 rounded-lg border border-yellow-200">
                    <div class="text-4xl mb-4 text-yellow-600">
                        <i class="fas fa-hands-helping"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Aides Sociales</h3>
                    <p class="text-gray-700 mb-4">
                        Allocations familiales, cartes de handicapés, aides sociales, bourses étudiantes...
                    </p>
                    <a href="{{ route('register') }}" class="text-yellow-600 font-semibold hover:text-yellow-800">En savoir plus →</a>
                </div>

                <!-- Service 5 -->
                <div class="service-card bg-gradient-to-br from-red-50 to-red-100 p-8 rounded-lg border border-red-200">
                    <div class="text-4xl mb-4 text-red-600">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Paiements & Taxes</h3>
                    <p class="text-gray-700 mb-4">
                        Paiement de taxes, amendes, redevances municipales, factures administratives...
                    </p>
                    <a href="{{ route('register') }}" class="text-red-600 font-semibold hover:text-red-800">En savoir plus →</a>
                </div>

                <!-- Service 6 -->
                <div class="service-card bg-gradient-to-br from-indigo-50 to-indigo-100 p-8 rounded-lg border border-indigo-200">
                    <div class="text-4xl mb-4 text-indigo-600">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Plaintes & Suggestions</h3>
                    <p class="text-gray-700 mb-4">
                        Signaler des problèmes, faire des suggestions, demander des informations...
                    </p>
                    <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:text-indigo-800">En savoir plus →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Informations Section -->
    <section class="py-20 bg-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">À Propos de MAIRI</h2>
                <p class="text-xl text-gray-600">{{ \App\Models\PlatformSettings::get('about_slogan', 'Transformant les services municipaux pour le bien de tous') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="text-5xl text-blue-600 mb-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ \App\Models\PlatformSettings::get('stat_citizens', '50,000+') }}</h3>
                    <p class="text-gray-600">Citoyens actifs utilisant notre plateforme</p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="text-5xl text-green-600 mb-4">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ \App\Models\PlatformSettings::get('stat_requests', '100,000+') }}</h3>
                    <p class="text-gray-600">Demandes traitées avec succès</p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="text-5xl text-purple-600 mb-4">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ \App\Models\PlatformSettings::get('stat_availability', '24/7') }}</h3>
                    <p class="text-gray-600">Service disponible en permanence</p>
                </div>
            </div>

            <div class="bg-white p-12 rounded-lg shadow-lg">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">Pourquoi nous choisir ?</h3>
                <ul class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-check text-green-600 text-xl mt-1"></i>
                        <span class="text-gray-700"><strong>Efficacité :</strong> Traitez vos demandes plus rapidement que jamais</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-check text-green-600 text-xl mt-1"></i>
                        <span class="text-gray-700"><strong>Transparence :</strong> Suivi complet de votre dossier</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-check text-green-600 text-xl mt-1"></i>
                        <span class="text-gray-700"><strong>Sécurité :</strong> Protection complète de vos données</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-check text-green-600 text-xl mt-1"></i>
                        <span class="text-gray-700"><strong>Accessibilité :</strong> Accessible sur tous les appareils</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-check text-green-600 text-xl mt-1"></i>
                        <span class="text-gray-700"><strong>Support :</strong> Équipe d'assistance toujours prête</span>
                    </li>
                    <li class="flex items-start space-x-3">
                        <i class="fas fa-check text-green-600 text-xl mt-1"></i>
                        <span class="text-gray-700"><strong>Gratuit :</strong> Service sans frais supplémentaires</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Popular Request Types Section -->
    <section class="py-16 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Types de Demandes Populaires</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Découvrez les services administratifs les plus demandés par les citoyens sénégalais
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Popular Type 1 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-blue-100 p-3 rounded-full mr-4">
                            <i class="fas fa-id-card text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Carte d'Identité</h3>
                            <span class="text-sm text-gray-500">État Civil</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Demande de carte d'identité nationale ou renouvellement. Délai estimé: 15 jours.
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600">Frais: 10 000 FCFA</span>
                        <a href="{{ route('register') }}" class="text-blue-600 text-sm font-medium hover:text-blue-800">Faire une demande →</a>
                    </div>
                </div>

                <!-- Popular Type 2 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <i class="fas fa-building text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Permis de Construire</h3>
                            <span class="text-sm text-gray-500">Urbanisme</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Autorisation de construire ou rénover un bâtiment. Délai estimé: 30 jours.
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600">Frais: 50 000 FCFA</span>
                        <a href="{{ route('register') }}" class="text-green-600 text-sm font-medium hover:text-green-800">Faire une demande →</a>
                    </div>
                </div>

                <!-- Popular Type 3 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 p-3 rounded-full mr-4">
                            <i class="fas fa-store text-purple-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Licence Commerciale</h3>
                            <span class="text-sm text-gray-500">Commerce</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Autorisation d'exercer une activité commerciale. Délai estimé: 7 jours.
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600">Frais: 25 000 FCFA</span>
                        <a href="{{ route('register') }}" class="text-purple-600 text-sm font-medium hover:text-purple-800">Faire une demande →</a>
                    </div>
                </div>

                <!-- Popular Type 4 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-yellow-100 p-3 rounded-full mr-4">
                            <i class="fas fa-passport text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Passeport</h3>
                            <span class="text-sm text-gray-500">État Civil</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Demande de passeport biométrique ou renouvellement. Délai estimé: 21 jours.
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600">Frais: 75 000 FCFA</span>
                        <a href="{{ route('register') }}" class="text-yellow-600 text-sm font-medium hover:text-yellow-800">Faire une demande →</a>
                    </div>
                </div>

                <!-- Popular Type 5 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-red-100 p-3 rounded-full mr-4">
                            <i class="fas fa-users text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Allocations Familiales</h3>
                            <span class="text-sm text-gray-500">Aides Sociales</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Demande d'allocations familiales et aides sociales. Délai estimé: 14 jours.
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600">Gratuit</span>
                        <a href="{{ route('register') }}" class="text-red-600 text-sm font-medium hover:text-red-800">Faire une demande →</a>
                    </div>
                </div>

                <!-- Popular Type 6 -->
                <div class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow">
                    <div class="flex items-center mb-4">
                        <div class="bg-indigo-100 p-3 rounded-full mr-4">
                            <i class="fas fa-graduation-cap text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Bourse Étudiante</h3>
                            <span class="text-sm text-gray-500">Aides Sociales</span>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-4">
                        Demande de bourse d'études ou aide financière. Délai estimé: 30 jours.
                    </p>
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-medium text-green-600">Gratuit</span>
                        <a href="{{ route('register') }}" class="text-indigo-600 text-sm font-medium hover:text-indigo-800">Faire une demande →</a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('register') }}" class="bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                    Voir Tous les Services
                </a>
            </div>
        </div>
    </section>

    <!-- Contacts Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Nous Contacter</h2>
                <p class="text-xl text-gray-600">Plusieurs façons de nous joindre pour vos questions</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Contact Info -->
                <div class="space-y-8">
                    <!-- Adresse -->
                    <div class="flex items-start space-x-4">
                        <div class="text-3xl text-blue-600">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Adresse</h3>
                            <p class="text-gray-700">{{ \App\Models\PlatformSettings::get('app_address', 'Mairie de Dakar') }}</p>
                        </div>
                    </div>

                    <!-- Téléphone -->
                    <div class="flex items-start space-x-4">
                        <div class="text-3xl text-green-600">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Téléphone</h3>
                            <p class="text-gray-700">
                                <a href="tel:{{ \App\Models\PlatformSettings::get('app_phone', '+221332246500') }}" class="hover:text-blue-600">
                                    {{ \App\Models\PlatformSettings::get('app_phone', '+221 33 224 6500') }}
                                </a>
                            </p>
                            <p class="text-gray-700">{{ \App\Models\PlatformSettings::get('hours_weekday', 'Lun-Ven : 8h00 - 18h00') }}</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start space-x-4">
                        <div class="text-3xl text-purple-600">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Email</h3>
                            <p class="text-gray-700">
                                <a href="mailto:{{ \App\Models\PlatformSettings::get('app_email', 'contact@mairi.sn') }}" class="hover:text-blue-600">
                                    {{ \App\Models\PlatformSettings::get('app_email', 'contact@mairi.sn') }}
                                </a>
                            </p>
                            <p class="text-gray-700">Réponse sous 24h</p>
                        </div>
                    </div>

                    <!-- Horaires -->
                    <div class="flex items-start space-x-4">
                        <div class="text-3xl text-red-600">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Horaires</h3>
                            <p class="text-gray-700">{{ \App\Models\PlatformSettings::get('hours_weekday', 'Lundi - Vendredi : 8h00 - 18h00') }}</p>
                            <p class="text-gray-700">{{ \App\Models\PlatformSettings::get('hours_saturday', 'Samedi : 9h00 - 14h00') }}</p>
                            <p class="text-gray-700">{{ \App\Models\PlatformSettings::get('hours_sunday', 'Dimanche : Fermé') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="bg-gray-50 p-8 rounded-lg border border-gray-200">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Formulaire de Contact</h3>
                    <form class="space-y-4" action="mailto:contact@mairi.sn" method="POST" enctype="text/plain">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Nom</label>
                            <input type="text" name="nom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email</label>
                            <input type="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Objet</label>
                            <input type="text" name="objet" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Message</label>
                            <textarea name="message" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-600" required></textarea>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 transition">
                            Envoyer le message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Social Media & Call to Action Section -->
    <section class="py-20 bg-blue-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold mb-8">Suivez-nous sur les Réseaux Sociaux</h2>
            <p class="text-xl mb-12">Restez informé des dernières actualités et mises à jour de la mairie</p>

            <!--@php
                $facebook = \App\Models\PlatformSettings::get('social_facebook', 'https://www.facebook.com/mairi.dakar');
                $twitter = \App\Models\PlatformSettings::get('social_twitter', 'https://www.twitter.com/mairi_dakar');
                $instagram = \App\Models\PlatformSettings::get('social_instagram', 'https://www.instagram.com/mairi_dakar');
                $linkedin = \App\Models\PlatformSettings::get('social_linkedin', 'https://www.linkedin.com/company/mairi-dakar');
                $youtube = \App\Models\PlatformSettings::get('social_youtube', 'https://www.youtube.com/@mairi_dakar');
                @endphp
                
                @if($facebook)
                <a href="{{ $facebook }}" target="_blank" rel="noopener noreferrer" class="bg-white text-blue-600 p-4 rounded-full hover:bg-gray-100 transition text-2xl" title="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                @endif
                
                @if($twitter)
                <a href="{{ $twitter }}" target="_blank" rel="noopener noreferrer" class="bg-white text-blue-400 p-4 rounded-full hover:bg-gray-100 transition text-2xl" title="Twitter">
                    <i class="fab fa-twitter"></i>
                </a>
                @endif
                
                @if($instagram)
                <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" class="bg-white text-pink-600 p-4 rounded-full hover:bg-gray-100 transition text-2xl" title="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                @endif
                
                @if($linkedin)
                <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer" class="bg-white text-blue-700 p-4 rounded-full hover:bg-gray-100 transition text-2xl" title="LinkedIn">
                    <i class="fab fa-linkedin-in"></i>
                </a>
                @endif
                
                @if($youtube)
                <a href="{{ $youtube }}" target="_blank" rel="noopener noreferrer" class="bg-white text-red-600 p-4 rounded-full hover:bg-gray-100 transition text-2xl" title="YouTube">
                    <i class="fab fa-youtube"></i>
                </a>
                @endif
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                            Accéder à mon espace
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="bg-white text-blue-600 px-8 py-3 rounded-lg font-bold hover:bg-gray-100 transition">
                            Se connecter
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="border-2 border-white text-white px-8 py-3 rounded-lg font-bold hover:bg-white hover:text-blue-600 transition">
                                Créer un compte maintenant
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
                <div>
                    <div class="flex items-center space-x-2 mb-4">
                        <i class="fas fa-city text-blue-400 text-2xl"></i>
                        <span class="text-2xl font-bold text-white">MAIRI</span>
                    </div>
                    <p class="text-sm">Plateforme numérique des services municipaux</p>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Services</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-blue-400">Demandes</a></li>
                        <li><a href="#" class="hover:text-blue-400">Paiements</a></li>
                        <li><a href="#" class="hover:text-blue-400">Support</a></li>
                        <li><a href="#" class="hover:text-blue-400">FAQ</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Ressources</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-blue-400">Documentation</a></li>
                        <li><a href="#" class="hover:text-blue-400">Guides</a></li>
                        <li><a href="#" class="hover:text-blue-400">Blog</a></li>
                        <li><a href="#" class="hover:text-blue-400">Tutoriels</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-bold text-white mb-4">Légal</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#" class="hover:text-blue-400">Conditions</a></li>
                        <li><a href="#" class="hover:text-blue-400">Confidentialité</a></li>
                        <li><a href="#" class="hover:text-blue-400">Cookies</a></li>
                        <li><a href="#" class="hover:text-blue-400">Contact</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 pt-8">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-center md:text-left">
                        &copy; 2026 MAIRI - Mairie de Dakar. Tous droits réservés.
                    </p>
                    <div class="flex space-x-4 mt-4 md:mt-0">
                        <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-gray-400 hover:text-blue-400"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
        </header>
        <div class="flex items-center justify-center w-full transition-opacity opacity-100 duration-750 lg:grow starting:opacity-0">

        </div>

        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif
    </body>
</html>
