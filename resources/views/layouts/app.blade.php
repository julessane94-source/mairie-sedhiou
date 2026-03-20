<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mairi')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand-deep: #0f172a;
            --brand-ocean: #0e7490;
            --brand-sky: #0369a1;
            --panel: #ffffff;
            --ink: #0f172a;
            --muted: #64748b;
        }
        body {
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 0% 0%, #dbeafe 0%, #f8fafc 45%, #f8fafc 100%);
        }
    </style>
</head>
<body>
    <header class="sticky top-0 z-30 border-b border-slate-200/70 bg-white/85 backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-3">
                <div class="grid h-10 w-10 place-content-center rounded-xl bg-gradient-to-br from-sky-600 to-cyan-500 text-lg font-black text-white shadow-lg">M</div>
                <div>
                    <h1 class="text-lg font-extrabold leading-tight text-slate-900">Mairi</h1>
                    <p class="text-xs text-slate-500">Plateforme municipale</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <div class="hidden rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 md:block">
                    Connecté: <span class="font-semibold text-slate-800">{{ auth()->user()->name }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-bold text-rose-700 transition hover:bg-rose-100">Déconnexion</button>
                </form>
            </div>
        </div>
    </header>

    <div class="mx-auto grid min-h-[calc(100vh-65px)] max-w-7xl grid-cols-1 gap-6 px-4 py-6 lg:grid-cols-[260px_1fr]">
        <aside class="h-fit rounded-3xl border border-slate-200 bg-gradient-to-b from-slate-900 to-slate-800 p-4 text-slate-100 shadow-xl">
            <p class="mb-3 px-2 text-xs font-semibold uppercase tracking-[0.2em] text-slate-400">Navigation</p>
            <nav class="space-y-1 text-sm">
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Dashboard</a>
                    <a href="{{ route('admin.utilisateurs.index') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Utilisateurs</a>
                    <a href="{{ route('admin.citoyens.index') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Citoyens</a>
                    <a href="{{ route('admin.demandes.index') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Demandes</a>
                    <a href="{{ route('admin.settings.operations') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Services mairie</a>
                    <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Mon profil</a>
                @elseif(auth()->user()->isCitoyen())
                    <a href="{{ route('citoyen.dashboard') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Dashboard</a>
                    <a href="{{ route('citoyen.demandes.index') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Mes demandes</a>
                    <a href="{{ route('citoyen.demandes.create') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Nouvelle demande</a>
                    <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Mon profil</a>
                @elseif(auth()->user()->isAgent())
                    <a href="{{ route('agent.dashboard') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Dashboard</a>
                    <a href="{{ route('agent.messages.index') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Messages</a>
                    <a href="{{ route('agent.demandes.index') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Demandes</a>
                    <a href="{{ route('profile.edit') }}" class="block rounded-xl px-3 py-2 font-semibold transition hover:bg-white/10">Mon profil</a>
                @endif
            </nav>
        </aside>

        <main class="space-y-4">
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="rounded-3xl border border-slate-200 bg-white/90 p-6 shadow-sm backdrop-blur">
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
