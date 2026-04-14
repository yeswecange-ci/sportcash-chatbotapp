<?php

namespace App\Http\Controllers\Kash;

use App\Http\Controllers\Controller;
use App\Models\BotEscalade;
use App\Models\User;
use Illuminate\Http\Request;

class EscaladeController extends Controller
{
    public function index(Request $request)
    {
        $query = BotEscalade::with('agent')->latest();

        if ($s = $request->get('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhere('identifiant', 'like', "%{$s}%")
                  ->orWhere('raison', 'like', "%{$s}%");
            });
        }

        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        $escalades = $query->paginate(20)->withQueryString();
        $agents    = User::orderBy('name')->get();

        return view('kash.escalades.index', compact('escalades', 'agents'));
    }

    public function show(BotEscalade $escalade)
    {
        $escalade->load('agent');
        $agents = User::orderBy('name')->get();
        return view('kash.escalades.show', compact('escalade', 'agents'));
    }

    public function updateStatut(Request $request, BotEscalade $escalade)
    {
        $request->validate(['statut' => 'required|in:en_attente,en_cours,resolue,fermee']);

        $escalade->update(['statut' => $request->statut]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'statut' => $escalade->statut]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function assign(Request $request, BotEscalade $escalade)
    {
        $request->validate(['assigned_to' => 'nullable|exists:users,id']);

        $escalade->update([
            'assigned_to' => $request->assigned_to ?: null,
            'statut'      => $escalade->statut === 'en_attente' ? 'en_cours' : $escalade->statut,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Assignation mise à jour.');
    }

    public function updateNotes(Request $request, BotEscalade $escalade)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);

        $escalade->update(['notes' => $request->notes]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Notes enregistrées.');
    }
}
