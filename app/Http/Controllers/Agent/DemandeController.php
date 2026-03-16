<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class DemandeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:agent');
    }

    public function index(): View
    {
        $demandesAssignees = auth()->user()->demandesAssignees()
            ->latest()
            ->paginate(15);

        $demandesPendantes = Demande::where('statut', 'pendante')
            ->whereNull('agent_assigne_id')
            ->latest()
            ->paginate(15);

        return view('agent.demandes.index', [
            'demandesAssignees' => $demandesAssignees,
            'demandesPendantes' => $demandesPendantes,
        ]);
    }

    public function show(Demande $demande): View
    {
        $demande->load('citoyen', 'agentAssigne', 'messages.expediteur');
        
        return view('agent.demandes.show', ['demande' => $demande]);
    }

    public function assigner(Demande $demande): RedirectResponse
    {
        $demande->update([
            'agent_assigne_id' => auth()->id(),
            'statut' => 'en_cours',
        ]);

        return redirect()->back()->with('success', 'Demande assignée avec succès');
    }

    public function accepter(Request $request, Demande $demande): RedirectResponse
    {
        $demande->update([
            'statut' => 'acceptee',
        ]);

        return redirect()->back()->with('success', 'Demande acceptée avec succès');
    }

    public function rejeter(Request $request, Demande $demande): RedirectResponse
    {
        $validated = $request->validate([
            'motif_rejet' => 'required|string',
        ]);

        $demande->update([
            'statut' => 'rejetee',
            'motif_rejet' => $validated['motif_rejet'],
        ]);

        return redirect()->back()->with('success', 'Demande rejetée avec succès');
    }
}
