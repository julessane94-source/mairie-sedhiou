<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Enums\DemandeType;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class DemandeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:citoyen'),
        ];
    }

    public function index(): View
    {
        // Filtrer les demandes du ressort de la mairie seulement
        $municipalTypes = collect(DemandeType::enabledMunicipalTypes())->pluck('value');
        $demandes = auth()->user()->demandes()
            ->whereIn('type', $municipalTypes)
            ->latest()
            ->paginate(10);

        return view('citoyen.demandes.index', ['demandes' => $demandes]);
    }

    public function create(): View
    {
        // Proposer uniquement les types de demandes du ressort de la mairie
        $typesDemandes = DemandeType::optionsGroupedMunicipal();
        return view('citoyen.demandes.create', compact('typesDemandes'));
    }

    public function store(Request $request): RedirectResponse
    {
        // Obtenir les types municipaux valides
        $municipalTypes = collect(DemandeType::enabledMunicipalTypes())->pluck('value')->toArray();
        
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|in:' . implode(',', $municipalTypes),
            'priorite' => 'nullable|in:basse,normale,haute,urgente',
        ]);

        $demande = auth()->user()->demandes()->create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'priorite' => $validated['priorite'] ?? 'normale',
            'statut' => 'pendante',
        ]);

        // Initialiser les informations du type de demande
        $demande->initialiserTypeDemande();
        $demande->calculerDateLimite();

        return redirect()->route('citoyen.demandes.show', $demande)
            ->with('success', 'Demande créée avec succès');
    }

    public function show(Demande $demande): View
    {
       \Illuminate\Support\Facades\Gate::authorize('view', $demande);
        $demande->load('messages.expediteur', 'agentAssigne');

        return view('citoyen.demandes.show', ['demande' => $demande]);
    }
}
