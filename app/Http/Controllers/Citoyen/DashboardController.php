<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:citoyen');
    }

    public function index(): View
    {
        $user = auth()->user();
        $user->load('demandes', 'profil');
        
        $demandes = $user->demandes()->latest()->paginate(10);
        $demandesPendantes = $user->demandes()
            ->where('statut', 'pendante')
            ->count();
        $demandesAcceptees = $user->demandes()
            ->where('statut', 'acceptee')
            ->count();
        $demandesRejetees = $user->demandes()
            ->where('statut', 'rejetee')
            ->count();

        return view('citoyen.dashboard', [
            'demandes' => $demandes,
            'demandesPendantes' => $demandesPendantes,
            'demandesAcceptees' => $demandesAcceptees,
            'demandesRejetees' => $demandesRejetees,
        ]);
    }
}
