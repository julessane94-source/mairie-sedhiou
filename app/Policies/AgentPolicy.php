<?php

namespace App\Policies;

use App\Models\User;

class AgentPolicy
{
    /**
     * Déterminer si l'utilisateur peut voir les agents
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut voir un agent
     */
    public function view(User $user, User $agent): bool
    {
        return $user->hasRole('admin') || $user->id === $agent->id;
    }

    /**
     * Déterminer si l'utilisateur peut créer un agent
     */
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut mettre à jour un agent
     */
    public function update(User $user, User $agent): bool
    {
        return $user->hasRole('admin') || $user->id === $agent->id;
    }

    /**
     * Déterminer si l'utilisateur peut supprimer un agent
     */
    public function delete(User $user, User $agent): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut changer le statut d'un agent
     */
    public function changeStatus(User $user, User $agent): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut assigner une demande à un agent
     */
    public function assignRequest(User $user, User $agent): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * Déterminer si l'utilisateur peut voir les demandes d'un agent
     */
    public function viewRequests(User $user, User $agent): bool
    {
        return $user->hasRole('admin') || $user->id === $agent->id;
    }

    /**
     * Déterminer si l'utilisateur peut voir la présence d'un agent
     */
    public function viewAttendance(User $user, User $agent): bool
    {
        return $user->hasRole('admin') || $user->id === $agent->id;
    }

    /**
     * Déterminer si l'utilisateur peut marquer la présence
     */
    public function markAttendance(User $user, User $agent): bool
    {
        return $user->hasRole('admin') || $user->id === $agent->id;
    }
}
