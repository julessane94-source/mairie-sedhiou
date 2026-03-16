<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterCitizenRequest;
use App\Models\User;
use App\Services\CitizenNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AuthenticationController extends Controller
{
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Traite la tentative de connexion avec rate limiting
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            return redirect()->intended(
                route(auth()->user()->role . '.dashboard')
            );
        }

        return back()
            ->withErrors(['email' => 'Identifiants invalides'])
            ->onlyInput('email');
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Enregistre un nouvel utilisateur
     */
    public function register(RegisterCitizenRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Générer le numéro de citoyen
        $numeroCitoyen = CitizenNumberGenerator::generate(
            $validated['date_naissance'],
            $validated['numero_registre']
        );

        // Créer l'utilisateur
        $user = User::create([
            'name' => "{$validated['prenom']} {$validated['nom']}",
            'prenom' => $validated['prenom'],
            'nom' => $validated['nom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'numero_citoyen' => $numeroCitoyen,
            'role' => 'citoyen',
            'statut' => 'actif',
        ]);

        // Créer le profil associé
        $user->profil()->create([
            'date_naissance' => $validated['date_naissance'],
            'lieu_naissance' => $validated['lieu_naissance'],
            'numero_registre' => $validated['numero_registre'],
            'adresse' => $validated['adresse'],
        ]);

        auth()->login($user);

        return redirect()->route('citoyen.dashboard');
    }

    /**
     * Déconnecte l'utilisateur
     */
    public function logout(Request $request): RedirectResponse
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
