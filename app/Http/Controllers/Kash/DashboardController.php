<?php

namespace App\Http\Controllers\Kash;

use App\Http\Controllers\Controller;
use App\Models\BotEscalade;
use App\Models\BotReclamation;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '7'); // jours

        $since = now()->subDays((int) $period)->startOfDay();

        // ── Réclamations ─────────────────────────────────────────────────────
        $totalReclamations   = BotReclamation::where('created_at', '>=', $since)->count();
        $enAttente           = BotReclamation::where('statut', 'en_attente')->count();
        $enCours             = BotReclamation::where('statut', 'en_cours')->count();
        $resolues            = BotReclamation::where('statut', 'resolue')->where('created_at', '>=', $since)->count();
        $haute               = BotReclamation::where('priorite', 'haute')->where('created_at', '>=', $since)->count();

        $parCanal = BotReclamation::where('created_at', '>=', $since)
            ->selectRaw('canal, COUNT(*) as total')
            ->groupBy('canal')
            ->pluck('total', 'canal')
            ->toArray();

        $parJour = BotReclamation::where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as jour, COUNT(*) as total')
            ->groupBy('jour')
            ->orderBy('jour')
            ->get()
            ->map(fn($r) => ['jour' => $r->jour, 'total' => $r->total]);

        // ── Escalades ────────────────────────────────────────────────────────
        $totalEscalades    = BotEscalade::where('created_at', '>=', $since)->count();
        $escaladeEnAttente = BotEscalade::where('statut', 'en_attente')->count();
        $escaladeResolues  = BotEscalade::where('statut', 'resolue')->where('created_at', '>=', $since)->count();

        $tauxEscalade = $totalReclamations + $totalEscalades > 0
            ? round($totalEscalades / ($totalReclamations + $totalEscalades) * 100, 1)
            : 0;

        // ── Dernières activités ───────────────────────────────────────────────
        $dernieresReclamations = BotReclamation::with('agent')
            ->latest()->limit(5)->get();

        $dernieresEscalades = BotEscalade::with('agent')
            ->latest()->limit(5)->get();

        return view('kash.dashboard', compact(
            'period',
            'totalReclamations', 'enAttente', 'enCours', 'resolues', 'haute',
            'parCanal', 'parJour',
            'totalEscalades', 'escaladeEnAttente', 'escaladeResolues', 'tauxEscalade',
            'dernieresReclamations', 'dernieresEscalades',
        ));
    }
}
