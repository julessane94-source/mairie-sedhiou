<?php
namespace App\Http\Controllers\Agent;
use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Message;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // On supprime le constructeur ici et on gère la sécurité dans les routes (plus propre)

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

        $assignedIds = $user->demandesAssignees()->pluck('id');

        $messagesRecus = Message::whereIn('demande_id', $assignedIds)
            ->where('expediteur_id', '!=', $user->id)
            ->with(['demande', 'expediteur'])
            ->latest()
            ->take(5)
            ->get();

        $messagesEnvoyes = Message::whereIn('demande_id', $assignedIds)
            ->where('expediteur_id', $user->id)
            ->with(['demande', 'expediteur'])
            ->latest()
            ->take(5)
            ->get();

        return view('agent.dashboard', compact(
            'demandesAssignees', 
            'demandesEnCours', 
            'demandesPendantes', 
            'demandesTerminees',
            'messagesRecus',
            'messagesEnvoyes'
        ));
    }
}