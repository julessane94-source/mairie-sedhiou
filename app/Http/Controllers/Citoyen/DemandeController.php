<?php

namespace App\Http\Controllers\Citoyen;

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
        $this->middleware('role:citoyen');
    }

    public function index(): View
    {
        $demandes = auth()->user()->demandes()
            ->latest()
            ->paginate(10);

        return view('citoyen.demandes.index', ['demandes' => $demandes]);
    }

    public function create(): View
    {
        return view('citoyen.demandes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string|max:100',
            'priorite' => 'nullable|in:basse,normale,haute,urgente',
        ]);

        $demande = auth()->user()->demandes()->create([
            'titre' => $validated['titre'],
            'description' => $validated['description'],
            'type' => $validated['type'],
            'priorite' => $validated['priorite'] ?? 'normale',
            'statut' => 'pendante',
        ]);

        return redirect()->route('citoyen.demandes.show', $demande)
            ->with('success', 'Demande créée avec succès');
    }

    public function show(Demande $demande): View
    {
        $this->authorize('view', $demande);
        $demande->load('messages.expediteur', 'agentAssigne');

        return view('citoyen.demandes.show', ['demande' => $demande]);
    }
}
