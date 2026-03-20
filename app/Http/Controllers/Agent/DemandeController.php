<?php

namespace App\Http\Controllers\Agent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Demande; // Très important pour pouvoir utiliser Demande::
use Illuminate\View\View;

class DemandeController extends Controller
{
    // Pas de constructeur ici pour Laravel 11

    public function index(): View
    {
        $user = auth()->user();

        // On récupère les demandes assignées à l'agent connecté
        $demandesAssignees = $user->demandesAssignees()
            ->latest()
            ->paginate(10);

        // On compte les demandes en attente de traitement global
        $demandesPendantes = Demande::where('statut', 'pendante')->paginate(10);

        return view('agent.demandes.index', compact('demandesAssignees', 'demandesPendantes'));
    }

public function assigner(Request $request, $id)
{
    // 1. Trouver la demande
    $demande = \App\Models\Demande::findOrFail($id);

    // 2. Logique d'assignation (exemple : changer l'ID de l'agent)
    // $demande->update(['agent_id' => auth()->id(), 'statut' => 'assignée']);

    return redirect()->back()->with('success', 'La demande a été assignée avec succès.');
}

public function show($id)
{
    // 1. Récupérer la demande depuis la base de données
    // (Assurez-vous d'importer le modèle avec : use App\Models\Demande;)
    $demande = \App\Models\Demande::findOrFail($id);

    // 2. Retourner une vue (vérifiez que ce fichier existe)
    return view('agent.demandes.show', compact('demande'));
}
}