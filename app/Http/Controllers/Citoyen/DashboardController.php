<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use App\Enums\DemandeType;
use App\Models\Message;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class DashboardController extends Controller implements HasMiddleware
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
        $user = auth()->user();
        $user->load('demandes', 'profil');
        
        // Filtrer les demandes du ressort de la mairie seulement
        $municipalTypes = collect(DemandeType::enabledMunicipalTypes())->pluck('value');
        $demandes = $user->demandes()
            ->whereIn('type', $municipalTypes)
            ->latest()
            ->paginate(10);
        
        $demandesPendantes = $user->demandes()
            ->whereIn('type', $municipalTypes)
            ->where('statut', 'pendante')
            ->count();
        
        $demandesAcceptees = $user->demandes()
            ->whereIn('type', $municipalTypes)
            ->where('statut', 'acceptee')
            ->count();
        
        $demandesRejetees = $user->demandes()
            ->whereIn('type', $municipalTypes)
            ->where('statut', 'rejetee')
            ->count();

        $demandesIds = $user->demandes()->whereIn('type', $municipalTypes)->pluck('id');

        // Messages reçus: écrits par d'autres utilisateurs sur les demandes du citoyen
        $messagesRecus = Message::whereIn('demande_id', $demandesIds)
            ->where('expediteur_id', '!=', $user->id)
            ->with(['demande', 'expediteur'])
            ->latest()
            ->take(5)
            ->get();

        // Messages envoyés: écrits par le citoyen sur ses demandes
        $messagesEnvoyes = Message::whereIn('demande_id', $demandesIds)
            ->where('expediteur_id', $user->id)
            ->with(['demande', 'expediteur'])
            ->latest()
            ->take(5)
            ->get();

        return view('citoyen.dashboard', [
            'demandes' => $demandes,
            'demandesPendantes' => $demandesPendantes,
            'demandesAcceptees' => $demandesAcceptees,
            'demandesRejetees' => $demandesRejetees,
            'messagesRecus' => $messagesRecus,
            'messagesEnvoyes' => $messagesEnvoyes,
        ]);
    }
}
