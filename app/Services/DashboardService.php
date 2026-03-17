<?php

namespace App\Services;

use App\Models\User;
use App\Models\Demande;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Récupère les statistiques globales optimisées (1 requête par entité)
     */
    public function getGlobalStats(): array
    {
        return [
            'utilisateurs' => $this->getUserStats(),
            'demandes' => $this->getDemandeStats(),
            'paiements' => $this->getPaymentStats(),
            'messages' => $this->getMessageStats(),
        ];
    }

    /**
     * Statistiques utilisateurs - 1 requête avec groupBy
     */
    private function getUserStats(): array
    {
        $stats = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->pluck('count', 'role');

        return [
            'total' => User::count(),
            'citoyens' => $stats['citoyen'] ?? 0,
            'agents' => $stats['agent'] ?? 0,
            'admins' => $stats['admin'] ?? 0,
        ];
    }

    /**
     * Statistiques demandes - 1 requête avec groupBy
     */
    private function getDemandeStats(): array
    {
        $stats = Demande::selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut');

        return [
            'total' => Demande::count(),
            'pendantes' => $stats['pendante'] ?? 0,
            'en_cours' => $stats['en_cours'] ?? 0,
            'acceptees' => $stats['acceptee'] ?? 0,
            'rejetees' => $stats['rejetee'] ?? 0,
        ];
    }

    /**
     * Statistiques paiements - 1 requête combinée
     */
    private function getPaymentStats(): array
    {
        $stats = Payment::selectRaw('statut, COUNT(*) as count, SUM(montant) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return [
            'total' => Payment::sum('montant') ?? 0,
            'pending' => Payment::where('statut', 'pending')->count(),
            'paid' => Payment::where('statut', 'paid')->count(),
            'cancelled' => Payment::where('statut', 'cancelled')->count(),
        ];
    }

    /**
     * Statistiques messages - 1 requête
     */
    private function getMessageStats(): array
    {
        return [
            'total' => Message::count(),
            'non_lus' => Message::where('lu', false)->count(),
        ];
    }

    /**
     * Statistiques par agent optimisées - 1 requête au lieu de N+1
     */
    public function getAgentStats(): Collection
    {
        // Récupère agents avec stats pré-agrégées
        $agents = User::where('role', 'agent')
            ->with('demandes')
            ->withCount([
                'demandes',
                'demandes as demandes_en_cours' => fn($q) => $q->where('statut', 'en_cours'),
                'demandes as demandes_acceptees' => fn($q) => $q->where('statut', 'acceptee'),
                'demandes as demandes_rejetees' => fn($q) => $q->where('statut', 'rejetee'),
            ])
            ->get();

        return $agents->map(function ($agent) {
            return [
                'agent' => $agent,
                'demandes_total' => $agent->demandes_count,
                'demandes_en_cours' => $agent->demandes_en_cours,
                'demandes_acceptees' => $agent->demandes_acceptees,
                'demandes_rejetees' => $agent->demandes_rejetees,
                'taux_reussite' => $this->calculateSuccessRate($agent),
            ];
        });
    }

    /**
     * Calcule le taux de réussite d'un agent
     */
    private function calculateSuccessRate(User $agent): float
    {
        $demandesAcceptees = $agent->demandes_acceptees ?? 0;
        $demandesTotal = $agent->demandes_count ?? 0;

        if ($demandesTotal === 0) {
            return 0;
        }

        return round(($demandesAcceptees / $demandesTotal) * 100, 2);
    }

    /**
     * Données récentes avec eager loading
     */
    public function getRecentData(): array
    {
        return [
            'dernieresDemandes' => Demande::with('citoyen')
                ->latest()
                ->limit(5)
                ->get(),
            'derniersPaiements' => Payment::with(['demande', 'citoyen'])
                ->latest()
                ->limit(5)
                ->get(),
            'derniersCitoyens' => User::where('role', 'citoyen')
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Diagnostics optimisés
     */
    public function getDiagnostics(): array
    {
        $diagnostics = [];

        // Agents chargés - optimisé avec whereHas et having
        $agentsCharges = User::where('role', 'agent')
            ->where('statut', 'actif')
            ->whereHas('demandes', function($q) {
                $q->selectRaw('citoyen_id')
                    ->groupBy('citoyen_id')
                    ->havingRaw('count(*) > 10');
            })
            ->get();

        if ($agentsCharges->count() > 0) {
            $diagnostics[] = [
                'type' => 'warning',
                'titre' => 'Agents surchargés',
                'message' => "{$agentsCharges->count()} agent(s) ont plus de 10 demandes assignées.",
                'action' => 'Rééquilibrer la charge de travail',
            ];
        }

        // Demandes en attente anciennes - 1 requête
        $demandesAncienne = Demande::where('statut', 'pendante')
            ->where('created_at', '<', now()->subDays(7))
            ->count();

        if ($demandesAncienne > 0) {
            $diagnostics[] = [
                'type' => 'error',
                'titre' => 'Demandes en attente',
                'message' => "{$demandesAncienne} demande(s) en attente depuis plus de 7 jours.",
                'action' => 'Assigner les demandes aux agents',
            ];
        }

        // Paiements non confirmés
        $paiementsNonConfirmes = Payment::where('statut', 'pending')
            ->where('created_at', '<', now()->subDays(3))
            ->count();

        if ($paiementsNonConfirmes > 0) {
            $diagnostics[] = [
                'type' => 'warning',
                'titre' => 'Paiements en attente',
                'message' => "{$paiementsNonConfirmes} paiement(s) en attente depuis plus de 3 jours.",
                'action' => 'Vérifier les paiements',
            ];
        }

        // Agents inactifs
        $agentsInactifs = User::where('role', 'agent')
            ->where('statut', 'inactif')
            ->count();

        if ($agentsInactifs > 0) {
            $diagnostics[] = [
                'type' => 'info',
                'titre' => 'Agents inactifs',
                'message' => "{$agentsInactifs} agent(s) marqué(s) comme inactif.",
                'action' => 'Gérer les agents',
            ];
        }

        // Absences non justifiées - 1 requête
        $absencesNonJustifiees = Attendance::where('date_presence', '>=', now()->subDays(30))
            ->where('statut', 'absent')
            ->whereNull('justificatif')
            ->count();

        if ($absencesNonJustifiees > 0) {
            $diagnostics[] = [
                'type' => 'warning',
                'titre' => 'Absences non justifiées',
                'message' => "{$absencesNonJustifiees} absence(s) non justifiée(s) ce mois-ci.",
                'action' => 'Demander justificatifs',
            ];
        }

        return $diagnostics;
    }

    /**
     * Présence du jour
     */
    public function getAttendanceToday(): array
    {
        $today = today();
        $attendance = Attendance::where('date_presence', $today)
            ->selectRaw('statut, COUNT(*) as count')
            ->groupBy('statut')
            ->pluck('count', 'statut');

        return [
            'presents' => $attendance['present'] ?? 0,
            'absents' => $attendance['absent'] ?? 0,
            'total_expected' => User::where('role', 'agent')->where('statut', 'actif')->count(),
        ];
    }

    /**
     * Graphiques de demandes
     */
    public function getDemandeCharts(): array
    {
        return [
            'parPriorite' => Demande::selectRaw('priorite, COUNT(*) as count')
                ->groupBy('priorite')
                ->pluck('count', 'priorite'),
            'parStatut' => Demande::selectRaw('statut, COUNT(*) as count')
                ->groupBy('statut')
                ->pluck('count', 'statut'),
        ];
    }

    /**
     * Délai moyen de traitement
     */
    public function getDelaiMoyenTraitement(): float
    {
        $demandesCompletees = Demande::whereIn('statut', ['acceptee', 'rejetee'])
            ->whereNotNull('updated_at')
            ->get();

        if ($demandesCompletees->isEmpty()) {
            return 0;
        }

        $delaisEnJours = $demandesCompletees->map(function ($demande) {
            $delai = $demande->updated_at->diffInDays($demande->created_at);
            return $delai > 0 ? $delai : 1;
        });

        return round($delaisEnJours->avg(), 2);
    }

    /**
     * Taux de satisfaction (exemple basique)
     */
    public function getTauxSatisfaction(): float
    {
        $demandesAcceptees = Demande::where('statut', 'acceptee')->count();
        $demandesTotal = Demande::whereIn('statut', ['acceptee', 'rejetee'])->count();

        if ($demandesTotal === 0) {
            return 0;
        }

        return round(($demandesAcceptees / $demandesTotal) * 100, 2);
    }
}
