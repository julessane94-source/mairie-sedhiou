<?php

namespace App\Http\Controllers\Citoyen;

use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class MessageController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('role:citoyen'),
        ];
    }

    public function store(Request $request, Demande $demande): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('view', $demande);

        $validated = $request->validate([
            'contenu' => 'required|string',
        ]);

        Message::create([
            'demande_id' => $demande->id,
            'expediteur_id' => auth()->id(),
            'contenu' => $validated['contenu'],
            'type_expediteur' => 'citoyen',
        ]);

        return redirect()->back()->with('success', 'Message envoyé avec succès');
    }
}
