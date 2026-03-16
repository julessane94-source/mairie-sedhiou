<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Mairi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center py-12 px-4">
    <div class="w-full max-w-2xl">
        <div class="bg-white rounded-lg shadow-2xl p-8">
            <h1 class="text-3xl font-bold text-center text-gray-800 mb-2">Mairi</h1>
            <p class="text-center text-gray-600 mb-8">Créer votre compte citoyen</p>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                    <p class="font-semibold mb-2">Erreurs d'inscription</p>
                    <ul class="text-sm space-y-1">
                        @foreach($errors->all() as $error)
                            <li>• {{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.post') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Identité -->
                <div class="border-b pb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Identité</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Prénom <span class="text-red-500">*</span></label>
                            <input type="text" name="prenom" value="{{ old('prenom') }}" required
                                class="w-full px-4 py-2 border {{ $errors->has('prenom') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Jean">
                            @error('prenom')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Nom <span class="text-red-500">*</span></label>
                            <input type="text" name="nom" value="{{ old('nom') }}" required
                                class="w-full px-4 py-2 border {{ $errors->has('nom') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Dupont">
                            @error('nom')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Naissance -->
                <div class="border-b pb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Naissance</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Date de naissance <span class="text-red-500">*</span></label>
                            <input type="date" name="date_naissance" value="{{ old('date_naissance') }}" required
                                class="w-full px-4 py-2 border {{ $errors->has('date_naissance') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            @error('date_naissance')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Lieu de naissance <span class="text-red-500">*</span></label>
                            <input type="text" name="lieu_naissance" value="{{ old('lieu_naissance') }}" required
                                class="w-full px-4 py-2 border {{ $errors->has('lieu_naissance') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Dakar">
                            @error('lieu_naissance')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Registre Civil -->
                <div class="border-b pb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Registre Civil</h2>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">Numéro de registre <span class="text-red-500">*</span></label>
                        <input type="text" name="numero_registre" value="{{ old('numero_registre') }}" required
                            class="w-full px-4 py-2 border {{ $errors->has('numero_registre') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="SN-2024-123456">
                        <p class="text-gray-500 text-sm mt-1">Votre numéro de registre civil unique</p>
                        @error('numero_registre')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Contact -->
                <div class="border-b pb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Contact</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2 border {{ $errors->has('email') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="jean.dupont@example.com">
                            @error('email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Adresse <span class="text-red-500">*</span></label>
                            <input type="text" name="adresse" value="{{ old('adresse') }}" required
                                class="w-full px-4 py-2 border {{ $errors->has('adresse') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="123 rue des Fleurs, Dakar, Sénégal">
                            @error('adresse')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Sécurité -->
                <div class="pb-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4">Sécurité</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password" required
                                class="w-full px-4 py-2 border {{ $errors->has('password') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="••••••••">
                            <p class="text-gray-500 text-sm mt-1">Min. 8 caractères, 1 majuscule, 1 chiffre, 1 caractère spécial</p>
                            @error('password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">Confirmer le mot de passe <span class="text-red-500">*</span></label>
                            <input type="password" name="password_confirmation" required
                                class="w-full px-4 py-2 border {{ $errors->has('password_confirmation') ? 'border-red-500' : 'border-gray-300' }} rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="••••••••">
                            @error('password_confirmation')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200">
                    Créer mon compte
                </button>
            </form>

            <p class="text-center text-gray-700 mt-6">
                Déjà inscrit? <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-semibold">Se connecter</a>
            </p>
        </div>
    </div>
</body>
</html>
