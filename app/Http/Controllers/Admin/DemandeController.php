<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\View\View;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class DemandeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:admin'),
        ];
    }

    public function index(): View
    {
        $demandes = Demande::with('citoyen', 'agentAssigne')
            ->paginate(20);
        
        return view('admin.demandes.index', ['demandes' => $demandes]);
    }

    public function show(Demande $demande): View
    {
        $demande->load('citoyen', 'agentAssigne', 'messages.expediteur');
        return view('admin.demandes.show', ['demande' => $demande]);
    }
}
