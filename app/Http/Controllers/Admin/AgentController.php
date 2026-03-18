<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Demande;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class AgentController extends Controller
{
use AuthorizesRequests;    
/**
     * Afficher la liste des agents
     */
    public function index(): View
    {
        $agents = User::where('role', 'agent')
            ->withCount('demandes')
            ->paginate(15);

        return view('admin.agents.index', compact('agents'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(): View
    {
        return view('admin.agents.create');
    }

    /**
     * Créer un nouvel agent
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'specialite' => 'nullable|string|max:100',
            'date_embauche' => 'nullable|date',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $agent = User::create([
            ...$validated,
            'name' => "{$validated['prenom']} {$validated['nom']}",
            'role' => 'agent',
            'statut' => 'actif',
            'password' => bcrypt($validated['password']),
        ]);

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('success', "L'agent {$agent->nom} a été créé avec succès.");
    }

    /**
     * Afficher les détails d'un agent
     */
    public function show(User $agent): View
    {
        $this->authorize('view', $agent);

        $agent->load('demandes', 'attendances');

        // Statistiques
        $stats = [
            'demandes_assignees' => $agent->demandes()->count(),
            'demandes_en_cours' => $agent->demandes()->where('statut', 'en_cours')->count(),
            'demandes_acceptees' => $agent->demandes()->where('statut', 'acceptee')->count(),
            'demandes_rejetees' => $agent->demandes()->where('statut', 'rejetee')->count(),
            'presence_mois' => $agent->attendances()
                ->forMonth(now()->year, now()->month)
                ->present()
                ->count(),
            'absence_mois' => $agent->attendances()
                ->forMonth(now()->year, now()->month)
                ->absent()
                ->count(),
        ];

        return view('admin.agents.show', compact('agent', 'stats'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit(User $agent): View
    {
        $this->authorize('update', $agent);

        return view('admin.agents.edit', compact('agent'));
    }

    /**
     * Mettre à jour un agent
     */
    public function update(Request $request, User $agent): RedirectResponse
    {
        $this->authorize('update', $agent);

        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $agent->id,
            'telephone' => 'nullable|string|max:20',
            'adresse' => 'nullable|string|max:255',
            'specialite' => 'nullable|string|max:100',
            'date_embauche' => 'nullable|date',
        ]);

        $agent->update($validated);

        return redirect()
            ->route('admin.agents.show', $agent)
            ->with('success', "L'agent a été mis à jour avec succès.");
    }

    /**
     * Supprimer un agent
     */
    public function destroy(User $agent): RedirectResponse
    {
        $this->authorize('delete', $agent);

        $nom = $agent->nom;
        $agent->delete();

        return redirect()
            ->route('admin.agents.index')
            ->with('success', "L'agent {$nom} a été supprimé.");
    }

    /**
     * Assigner une demande à un agent
     */
    public function assignerDemande(Request $request, User $agent): RedirectResponse
    {
        $this->authorize('update', $agent);

        $validated = $request->validate([
            'demande_id' => 'required|exists:demandes,id',
        ]);

        $demande = Demande::findOrFail($validated['demande_id']);

        // Vérifier que la demande n'est pas déjà assignée
        if ($demande->agent_id) {
            return back()->with('error', 'Cette demande est déjà assignée à un autre agent.');
        }

        $demande->update([
            'agent_id' => $agent->id,
            'statut' => 'en_cours',
        ]);

        return back()
            ->with('success', "La demande a été assignée à {$agent->nom}.");
    }

    /**
     * Retirer un agent d'une demande
     */
    public function deasigner(Request $request, User $agent): RedirectResponse
    {
        $this->authorize('update', $agent);

        $validated = $request->validate([
            'demande_id' => 'required|exists:demandes,id',
        ]);

        $demande = Demande::findOrFail($validated['demande_id']);

        if ($demande->agent_id !== $agent->id) {
            return back()->with('error', 'Cet agent n\'est pas assigné à cette demande.');
        }

        $demande->update([
            'agent_id' => null,
            'statut' => 'pendante',
        ]);

        return back()
            ->with('success', 'L\'agent a été retiré de la demande.');
    }

    /**
     * Changer le statut d'un agent
     */
    public function changerStatut(Request $request, User $agent): RedirectResponse
    {
        $this->authorize('update', $agent);

        $validated = $request->validate([
            'statut' => 'required|in:actif,inactif,congé,suspendu',
        ]);

        $agent->update($validated);

        $statuts = [
            'actif' => 'Actif',
            'inactif' => 'Inactif',
            'congé' => 'En congé',
            'suspendu' => 'Suspendu',
        ];

        return back()
            ->with('success', "Le statut de {$agent->nom} est maintenant: {$statuts[$validated['statut']]}");
    }
}
