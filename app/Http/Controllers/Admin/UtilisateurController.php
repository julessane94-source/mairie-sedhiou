<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Profil;
use App\Models\User;
use App\Services\CitizenNumberGenerator;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class UtilisateurController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:admin'),
        ];
    }

    public function index(): View
    {
        $query = User::query()->with('profil');

        if (request()->filled('role')) {
            $query->where('role', request('role'));
        }

        if (request()->filled('statut')) {
            $query->where('statut', request('statut'));
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('numero_citoyen', 'like', "%{$search}%");
            });
        }

        $utilisateurs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.utilisateurs.index', ['utilisateurs' => $utilisateurs]);
    }

    public function citoyens(): View
    {
        $query = User::where('role', 'citoyen')->with('profil');

        if (request()->filled('statut')) {
            $query->where('statut', request('statut'));
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('numero_citoyen', 'like', "%{$search}%");
            });
        }

        $utilisateurs = $query->latest()->paginate(20)->withQueryString();

        return view('admin.utilisateurs.index', [
            'utilisateurs' => $utilisateurs,
            'citoyensOnly' => true,
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.utilisateurs.show', ['user' => $user]);
    }

    public function create(): View
    {
        return view('admin.utilisateurs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,citoyen,agent',
            'statut' => 'required|in:actif,inactif,suspendu',
        ]);

        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);

        // Assure un profil minimal pour tous les comptes
        Profil::firstOrCreate(['user_id' => $user->id], []);

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur créé avec succès');
    }

    public function edit(User $user): View
    {
        return view('admin.utilisateurs.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,citoyen,agent',
            'statut' => 'required|in:actif,inactif,suspendu',
            'prenom' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
        ]);

        // Si l'utilisateur est citoyen, mettre à jour le profil aussi
        if ($user->role === 'citoyen') {
            $profil = $user->profil ?? $user->profil()->create([]);
            $profil->update($request->only([
                'date_naissance',
                'lieu_naissance',
                'adresse',
                'numero_registre'
            ]));

            if ($profil->date_naissance && $profil->numero_registre) {
                $user->numero_citoyen = CitizenNumberGenerator::generate(
                    $profil->date_naissance->format('Y-m-d'),
                    $profil->numero_registre
                );
            }
        }

        $user->update($validated);
        $user->save();

        return redirect()->route('admin.utilisateurs.show', $user)->with('success', 'Utilisateur mis à jour avec succès');
    }

    public function destroy(User $user)
    {
        // Empêcher la suppression du dernier admin
        if ($user->role === 'admin' && User::where('role', 'admin')->count() <= 1) {
            return redirect()->back()->with('error', 'Impossible de supprimer le dernier administrateur');
        }

        $user->delete();

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur supprimé avec succès');
    }

    public function changerRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,citoyen,agent',
        ]);

        $user->update(['role' => $request->role]);

        return redirect()->back()->with('success', 'Rôle mis à jour avec succès');
    }

    public function changerStatut(Request $request, User $user)
    {
        $request->validate([
            'statut' => 'required|in:actif,inactif,suspendu',
        ]);

        $user->update(['statut' => $request->statut]);

        return redirect()->back()->with('success', 'Statut mis à jour avec succès');
    }
}
