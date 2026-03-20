<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class MessageController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $assignedIds = $user->demandesAssignees()->pluck('id');

        $messagesRecus = Message::whereIn('demande_id', $assignedIds)
            ->where('expediteur_id', '!=', $user->id)
            ->with(['demande', 'expediteur'])
            ->latest()
            ->paginate(15, ['*'], 'recus_page');

        $messagesEnvoyes = Message::whereIn('demande_id', $assignedIds)
            ->where('expediteur_id', $user->id)
            ->with(['demande', 'expediteur'])
            ->latest()
            ->paginate(15, ['*'], 'envoyes_page');

        return view('agent.messages.index', compact('messagesRecus', 'messagesEnvoyes'));
    }

    // Enregistre un nouveau message (lié à une demande)

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
