<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        // Middlewares définis dans routes/web.php - plus besoin de les redéclarer
        $this->dashboardService = $dashboardService;
    }

    public function index(): View
    {
        // Récupère toutes les données de manière optimisée
        $stats = $this->dashboardService->getGlobalStats();
        $agentStats = $this->dashboardService->getAgentStats();
        $recentData = $this->dashboardService->getRecentData();
        $diagnostics = $this->dashboardService->getDiagnostics();
        $attendance = $this->dashboardService->getAttendanceToday();
        $charts = $this->dashboardService->getDemandeCharts();
        $delaiMoyenTraitement = $this->dashboardService->getDelaiMoyenTraitement();
        $tauxSatisfaction = $this->dashboardService->getTauxSatisfaction();

        return view('admin.dashboard', [
            'stats' => $stats,
            'agentStats' => $agentStats,
            'dernieresDemandes' => $recentData['dernieresDemandes'],
            'derniersPaiements' => $recentData['derniersPaiements'],
            'derniersCitoyens' => $recentData['derniersCitoyens'],
            'diagnostics' => $diagnostics,
            'agentsPresents' => $attendance['presents'],
            'agentsAbsents' => $attendance['absents'],
            'demandesParPriorite' => $charts['parPriorite'],
            'demandesParStatut' => $charts['parStatut'],
            'delaiMoyenTraitement' => $delaiMoyenTraitement,
            'tauxSatisfaction' => $tauxSatisfaction,
        ]);
    }
}

