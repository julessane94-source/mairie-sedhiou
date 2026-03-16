<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mairi')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-blue-600">Mairi</h1>
            <div class="flex items-center space-x-4">
                <span class="text-gray-700">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-red-600 hover:text-red-800 font-semibold">Déconnexion</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-900 text-white p-6">
            <nav class="space-y-4">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Dashboard</a>
                    <a href="{{ route('admin.utilisateurs.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Utilisateurs</a>
                    <a href="{{ route('admin.demandes.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Demandes</a>
                @elseif(auth()->user()->isCitoyen())
                    <a href="{{ route('citoyen.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Dashboard</a>
                    <a href="{{ route('citoyen.demandes.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Mes demandes</a>
                    <a href="{{ route('citoyen.demandes.create') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Nouvelle demande</a>
                    <a href="{{ route('citoyen.profil.edit') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Mon profil</a>
                @elseif(auth()->user()->isAgent())
                    <a href="{{ route('agent.dashboard') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Dashboard</a>
                    <a href="{{ route('agent.demandes.index') }}" class="block px-4 py-2 rounded hover:bg-gray-800">Demandes</a>
                @endif
            </nav>
        </aside>

        <!-- Contenu principal -->
        <main class="flex-1 p-8">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>
