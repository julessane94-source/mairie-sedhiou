<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Demande;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    public function index(): View
    {
        $totalUtilisateurs = User::count();
        $totalCitoyens = User::where('role', 'citoyen')->count();
        $totalAgents = User::where('role', 'agent')->count();
        $totalDemandes = Demande::count();
        $demandesEnCours = Demande::where('statut', 'en_cours')->count();
        $demandesPendantes = Demande::where('statut', 'pendante')->count();

        $dernieresDemandes = Demande::latest()->limit(10)->get();

        return view('admin.dashboard', [
            'totalUtilisateurs' => $totalUtilisateurs,
            'totalCitoyens' => $totalCitoyens,
            'totalAgents' => $totalAgents,
            'totalDemandes' => $totalDemandes,
            'demandesEnCours' => $demandesEnCours,
            'demandesPendantes' => $demandesPendantes,
            'dernieresDemandes' => $dernieresDemandes,
        ]);
    }
}
