<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:agent');
    }

    public function store(Request $request, Demande $demande): RedirectResponse
    {
        $validated = $request->validate([
            'contenu' => 'required|string',
        ]);

        Message::create([
            'demande_id' => $demande->id,
            'expediteur_id' => auth()->id(),
            'contenu' => $validated['contenu'],
            'type_expediteur' => 'agent',
        ]);

        return redirect()->back()->with('success', 'Message envoyé avec succès');
    }
}
