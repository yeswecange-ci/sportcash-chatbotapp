<?php

namespace App\Http\Controllers\Kash;

use App\Http\Controllers\Controller;
use App\Models\BotReclamation;
use App\Models\User;
use Illuminate\Http\Request;

class ReclamationController extends Controller
{
    public function index(Request $request)
    {
        $query = BotReclamation::with('agent')->latest();

        if ($s = $request->get('search')) {
            $query->where(function ($q) use ($s) {
                $q->where('reference', 'like', "%{$s}%")
                  ->orWhere('identifiant', 'like', "%{$s}%")
                  ->orWhere('infos', 'like', "%{$s}%");
            });
        }

        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }

        if ($canal = $request->get('canal')) {
            $query->where('canal', $canal);
        }

        if ($priorite = $request->get('priorite')) {
            $query->where('priorite', $priorite);
        }

        $reclamations = $query->paginate(20)->withQueryString();
        $agents       = User::orderBy('name')->get();

        return view('kash.reclamations.index', compact('reclamations', 'agents'));
    }

    public function show(BotReclamation $reclamation)
    {
        $reclamation->load('agent');
        $agents = User::orderBy('name')->get();
        return view('kash.reclamations.show', compact('reclamation', 'agents'));
    }

    public function updateStatut(Request $request, BotReclamation $reclamation)
    {
        $request->validate(['statut' => 'required|in:en_attente,en_cours,resolue,fermee']);

        $reclamation->update(['statut' => $request->statut]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'statut' => $reclamation->statut]);
        }

        return back()->with('success', 'Statut mis à jour.');
    }

    public function assign(Request $request, BotReclamation $reclamation)
    {
        $request->validate(['assigned_to' => 'nullable|exists:users,id']);

        $reclamation->update([
            'assigned_to' => $request->assigned_to ?: null,
            'statut'      => $reclamation->statut === 'en_attente' ? 'en_cours' : $reclamation->statut,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Assignation mise à jour.');
    }

    public function updateNotes(Request $request, BotReclamation $reclamation)
    {
        $request->validate(['notes' => 'nullable|string|max:5000']);

        $reclamation->update(['notes' => $request->notes]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Notes enregistrées.');
    }
}
