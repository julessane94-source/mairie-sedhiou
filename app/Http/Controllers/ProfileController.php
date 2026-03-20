<?php

namespace App\Http\Controllers;

use App\Models\Profil;
use App\Services\CitizenNumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();
        if (!$user->profil) {
            Profil::create(['user_id' => $user->id]);
        }
        $user->load('profil');

        return view('profile.edit', ['user' => $user]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        $profilId = $user->profil?->id ?? 0;

        $userValidated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'prenom' => 'nullable|string|max:255',
            'nom' => 'nullable|string|max:255',
        ]);

        $profilRules = [
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'date_naissance' => 'nullable|date',
            'lieu_naissance' => 'nullable|string|max:255',
            'numero_registre' => 'nullable|string|max:50|unique:profils,numero_registre,' . $profilId,
            'num_id' => 'nullable|string|max:50',
            'type_id' => 'nullable|in:CNI,Passeport,Permis',
            'bio' => 'nullable|string|max:500',
        ];

        $profilValidated = $request->validate($profilRules);

        $user->update($userValidated);

        $profil = $user->profil ?? Profil::create(['user_id' => $user->id]);
        $profil->update($profilValidated);

        if ($user->role === 'citoyen' && $profil->date_naissance && $profil->numero_registre) {
            $user->numero_citoyen = CitizenNumberGenerator::generate(
                $profil->date_naissance->format('Y-m-d'),
                $profil->numero_registre
            );
            $user->save();
        }

        return back()->with('success', 'Profil mis a jour avec succes.');
    }
}
