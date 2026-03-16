<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:agent');
    }

    public function index(): View
    {
        $user = auth()->user();
        
        $demandesAssignees = $user->demandesAssignees()
            ->latest()
            ->paginate(10);

        $demandesEnCours = $user->demandesAssignees()
            ->where('statut', 'en_cours')
            ->count();

        $demandesPendantes = Demande::where('statut', 'pendante')
            ->whereNull('agent_assigne_id')
            ->count();

        $demandesTerminees = $user->demandesAssignees()
            ->whereIn('statut', ['acceptee', 'rejetee'])
            ->count();

        return view('agent.dashboard', [
            'demandesAssignees' => $demandesAssignees,
            'demandesEnCours' => $demandesEnCours,
            'demandesPendantes' => $demandesPendantes,
            'demandesTerminees' => $demandesTerminees,
        ]);
    }
}
