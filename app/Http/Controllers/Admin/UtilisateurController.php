<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Http\Request;

class UtilisateurController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(): View
    {
        $utilisateurs = User::paginate(20);
        return view('admin.utilisateurs.index', ['utilisateurs' => $utilisateurs]);
    }

    public function show(User $user): View
    {
        return view('admin.utilisateurs.show', ['user' => $user]);
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
