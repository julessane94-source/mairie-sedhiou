<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use App\Enums\DemandeType;
use Illuminate\Contracts\Auth\Guard;
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
        $municipalTypes = collect(DemandeType::municipalTypes())->pluck('value');
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

        // Récupérer les messages reçus (envoyés par d'autres utilisateurs)
        // sur les demandes du citoyen
        $messagesRecus = \App\Models\Message::whereIn(
            'demande_id',
            $user->demandes()->whereIn('type', $municipalTypes)->pluck('id')
        )
            ->where('expediteur_id', '!=', $user->id)
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
        ]);
    }
}
