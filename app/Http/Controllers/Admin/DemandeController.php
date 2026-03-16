<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use Illuminate\View\View;

class DemandeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
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
