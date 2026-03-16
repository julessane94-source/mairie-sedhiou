<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;

class DemandePolicy
{
    public function view(User $user, Demande $demande): bool
    {
        // Un citoyen peut voir ses propres demandes
        if ($user->isCitoyen()) {
            return $user->id === $demande->citoyen_id;
        }

        // Un agent peut voir les demandes qui lui sont assignées
        if ($user->isAgent()) {
            return $user->id === $demande->agent_assigne_id;
        }

        // Un admin peut voir toutes les demandes
        return $user->isAdmin();
    }

    public function update(User $user, Demande $demande): bool
    {
        // Seuls les agents/admins peuvent mettre à jour
        return $user->isAgent() || $user->isAdmin();
    }

    public function delete(User $user, Demande $demande): bool
    {
        // Seul l'admin peut supprimer
        return $user->isAdmin();
    }
}
