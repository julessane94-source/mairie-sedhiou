<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class ProfilController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:citoyen'),
        ];
    }

    public function edit(): View
    {
        $user = auth()->user()->load('profil');
        return view('citoyen.profil.edit', ['user' => $user]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();
        
        $userValidated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
        ]);

        $profilValidated = $request->validate([
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'ville' => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:10',
            'date_naissance' => 'nullable|date',
            'num_id' => 'nullable|string|max:50',
            'type_id' => 'nullable|in:CNI,Passeport,Permis',
            'bio' => 'nullable|string|max:500',
        ]);

        $user->update($userValidated);

        $profil = $user->profil ?? $user->profil()->create([]);
        $profil->update($profilValidated);

        return redirect()->back()->with('success', 'Profil mis à jour avec succès');
    }
}
