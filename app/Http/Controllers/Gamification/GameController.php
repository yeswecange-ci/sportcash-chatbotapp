<?php

namespace App\Http\Controllers\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\GameAnswer;
use App\Models\GameParticipation;
use App\Models\GameQuestion;
use App\Services\Gamification\TwilioFlowGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GameController extends Controller
{
    public function __construct(private TwilioFlowGeneratorService $flowGenerator) {}

    // ── Liste / Dashboard ─────────────────────────────────────────────────────

    public function index()
    {
        $games = Game::withCount([
            'participations',
            'participations as completed_count' => fn($q) => $q->where('status', 'completed'),
        ])->orderByDesc('created_at')->get();

        $globalStats = [
            'active_count'       => $games->where('status', 'active')->count(),
            'total_participants' => $games->sum('participations_count'),
            'total_completed'    => $games->sum('completed_count'),
            'total_games'        => $games->count(),
        ];

        return view('gamification.index', compact('games', 'globalStats'));
    }

    // ── Création ──────────────────────────────────────────────────────────────

    public function create()
    {
        return view('gamification.create', ['game' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'type'              => 'required|in:quiz,free_text,vote,prediction',
            'eligibility'       => 'required|in:all,clients_only',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'max_participants'  => 'nullable|integer|min:1',
            'thank_you_message' => 'nullable|string',
            'questions'         => 'nullable|array',
            'questions.*.text'  => 'required_with:questions|string',
            'questions.*.type'  => 'required_with:questions|in:mcq,free_text,vote,prediction',
            'questions.*.options'        => 'nullable|string',
            'questions.*.correct_answer' => 'nullable|string|max:255',
        ]);

        $validated['slug']   = Game::generateSlug($validated['name']);
        $validated['status'] = 'draft';

        $questions = $validated['questions'] ?? [];
        unset($validated['questions']);

        $game = Game::create($validated);

        foreach ($questions as $i => $q) {
            if (empty(trim($q['text'] ?? ''))) {
                continue;
            }

            $options = null;
            if (!empty($q['options'])) {
                $options = array_values(array_filter(array_map('trim', explode("\n", $q['options']))));
            }

            GameQuestion::create([
                'game_id'        => $game->id,
                'order'          => $i + 1,
                'text'           => $q['text'],
                'type'           => $q['type'],
                'options'        => $options ?: null,
                'correct_answer' => $q['correct_answer'] ?? null,
            ]);
        }

        return redirect()->route('gamification.show', $game->slug)
            ->with('success', 'Jeu créé avec succès.');
    }

    // ── Affichage ─────────────────────────────────────────────────────────────

    public function show(string $slug)
    {
        // Charge le jeu avec questions + leurs réponses (pour questionStats)
        // Les participations sont chargées séparément avec pagination
        $game = Game::where('slug', $slug)
            ->with(['questions.answers'])
            ->firstOrFail();

        // Stats globales via requêtes DB directes (efficace, pas de chargement de toutes les participations)
        $stats = [
            'total'     => $game->participations()->count(),
            'completed' => $game->participations()->where('status', 'completed')->count(),
            'abandoned' => $game->participations()->where('status', 'abandoned')->count(),
            'started'   => $game->participations()->where('status', 'started')->count(),
        ];

        $stats['completion_rate'] = $stats['total'] > 0
            ? round($stats['completed'] / $stats['total'] * 100)
            : 0;

        // Score par question (les answers sont déjà chargées via with['questions.answers'])
        $questionStats = [];
        foreach ($game->questions as $question) {
            $answers = $question->answers;
            $total   = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            $wrong   = $answers->where('is_correct', false)->count();

            $questionStats[$question->id] = [
                'total'   => $total,
                'correct' => $correct,
                'wrong'   => $wrong,
                'null'    => $total - $correct - $wrong,
                'rate'    => $total > 0 ? round($correct / $total * 100) : null,
            ];
        }

        // Participations paginées (50 par page) avec leurs réponses
        $participations = $game->participations()
            ->with(['answers.question'])
            ->latest('started_at')
            ->paginate(50);

        return view('gamification.show', compact('game', 'stats', 'questionStats', 'participations'));
    }

    // ── Statistiques ──────────────────────────────────────────────────────────

    public function statistics(string $slug)
    {
        $game = Game::where('slug', $slug)
            ->with(['questions.answers'])
            ->firstOrFail();

        $participations = GameParticipation::where('game_id', $game->id)
            ->with('answers')
            ->get();

        $total     = $participations->count();
        $completed = $participations->where('status', 'completed')->count();
        $started   = $participations->where('status', 'started')->count();
        $abandoned = $participations->where('status', 'abandoned')->count();

        $completionRate = $total > 0 ? round($completed / $total * 100) : 0;

        // Score moyen parmi les complétés
        $avgScore = null;
        $totalQuestions = $game->questions->count();
        if ($completed > 0 && $totalQuestions > 0) {
            $totalCorrect = 0;
            foreach ($participations->where('status', 'completed') as $p) {
                $totalCorrect += $p->answers->where('is_correct', true)->count();
            }
            $avgScore = round($totalCorrect / ($completed * $totalQuestions) * 100);
        }

        // Stats par question
        $questionStats = [];
        foreach ($game->questions as $question) {
            $answers = $question->answers;
            $qTotal  = $answers->count();
            $correct = $answers->where('is_correct', true)->count();
            $wrong   = $answers->where('is_correct', false)->count();
            $nullC   = $qTotal - $correct - $wrong;

            // Distribution des réponses brutes
            $distribution = $answers
                ->groupBy(fn($a) => mb_strtolower(trim($a->answer_text)))
                ->map(fn($group) => [
                    'text'  => $group->first()->answer_text,
                    'count' => $group->count(),
                ])
                ->sortByDesc('count')
                ->values()
                ->toArray();

            $questionStats[] = [
                'question'     => $question,
                'total'        => $qTotal,
                'correct'      => $correct,
                'wrong'        => $wrong,
                'null_count'   => $nullC,
                'rate'         => $qTotal > 0 ? round($correct / $qTotal * 100) : null,
                'distribution' => $distribution,
            ];
        }

        // Funnel
        $atLeastOneCorrect = 0;
        foreach ($participations as $p) {
            if ($p->answers->where('is_correct', true)->count() > 0) {
                $atLeastOneCorrect++;
            }
        }

        $funnel = [
            'started'            => $total,
            'completed'          => $completed,
            'at_least_1_correct' => $atLeastOneCorrect,
        ];

        // Évolution temporelle : participations par jour
        $timeline = DB::table('game_participations')
            ->selectRaw('DATE(started_at) as date, COUNT(*) as count')
            ->where('game_id', $game->id)
            ->whereNotNull('started_at')
            ->groupByRaw('DATE(started_at)')
            ->orderBy('date')
            ->get();

        return view('gamification.statistics', compact(
            'game', 'total', 'completed', 'started', 'abandoned',
            'completionRate', 'avgScore', 'questionStats', 'funnel', 'timeline'
        ));
    }

    // ── Édition ───────────────────────────────────────────────────────────────

    public function edit(string $slug)
    {
        $game = Game::where('slug', $slug)->with('questions')->firstOrFail();
        return view('gamification.create', compact('game'));
    }

    public function update(Request $request, string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'description'       => 'nullable|string',
            'type'              => 'required|in:quiz,free_text,vote,prediction',
            'eligibility'       => 'required|in:all,clients_only',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date|after_or_equal:start_date',
            'max_participants'  => 'nullable|integer|min:1',
            'thank_you_message' => 'nullable|string',
            'questions'         => 'nullable|array',
            'questions.*.text'  => 'required_with:questions|string',
            'questions.*.type'  => 'required_with:questions|in:mcq,free_text,vote,prediction',
            'questions.*.options'        => 'nullable|string',
            'questions.*.correct_answer' => 'nullable|string|max:255',
        ]);

        $questions = $validated['questions'] ?? null;
        unset($validated['questions']);

        $game->update($validated);

        if ($questions !== null) {
            $game->questions()->delete();
            foreach ($questions as $i => $q) {
                if (empty(trim($q['text'] ?? ''))) {
                    continue;
                }

                $options = null;
                if (!empty($q['options'])) {
                    $options = array_values(array_filter(array_map('trim', explode("\n", $q['options']))));
                }

                GameQuestion::create([
                    'game_id'        => $game->id,
                    'order'          => $i + 1,
                    'text'           => $q['text'],
                    'type'           => $q['type'],
                    'options'        => $options ?: null,
                    'correct_answer' => $q['correct_answer'] ?? null,
                ]);
            }
        }

        return redirect()->route('gamification.show', $game->slug)
            ->with('success', 'Jeu mis à jour.');
    }

    public function destroy(string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $game->delete();

        return redirect()->route('gamification.index')
            ->with('success', 'Jeu supprimé.');
    }

    // ── Questions ─────────────────────────────────────────────────────────────

    public function storeQuestion(Request $request, string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();

        $validated = $request->validate([
            'text'           => 'required|string',
            'type'           => 'required|in:mcq,free_text,vote,prediction',
            'options'        => 'nullable|string',
            'correct_answer' => 'nullable|string|max:255',
        ]);

        $maxOrder = $game->questions()->max('order') ?? 0;

        $options = null;
        if (!empty($validated['options'])) {
            $options = array_values(array_filter(array_map('trim', explode("\n", $validated['options']))));
        }

        GameQuestion::create([
            'game_id'        => $game->id,
            'order'          => $maxOrder + 1,
            'text'           => $validated['text'],
            'type'           => $validated['type'],
            'options'        => $options ?: null,
            'correct_answer' => $validated['correct_answer'] ?? null,
        ]);

        return redirect(route('gamification.edit', $slug) . '#questions')
            ->with('success', 'Question ajoutée.');
    }

    public function destroyQuestion(string $slug, int $id)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        GameQuestion::where('game_id', $game->id)->where('id', $id)->delete();

        $game->questions()->orderBy('order')->each(function ($q, $i) {
            $q->update(['order' => $i + 1]);
        });

        return redirect()->route('gamification.show', $slug)
            ->with('success', 'Question supprimée.');
    }

    // ── Statuts ───────────────────────────────────────────────────────────────

    public function activate(string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $game->update(['status' => 'active']);

        return back()->with('success', 'Jeu activé.');
    }

    public function close(string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        $game->update(['status' => 'closed']);

        // Abandonner toutes les participations encore en cours
        $abandoned = $game->participations()
            ->where('status', 'started')
            ->update(['status' => 'abandoned']);

        $msg = 'Jeu clôturé.';
        if ($abandoned > 0) {
            $msg .= " {$abandoned} participation(s) en cours marquée(s) comme abandonnée(s).";
        }

        return back()->with('success', $msg);
    }

    // ── Duplication ───────────────────────────────────────────────────────────

    public function duplicate(string $slug)
    {
        $original = Game::where('slug', $slug)->with('questions')->firstOrFail();

        $newGame = Game::create([
            'name'              => $original->name . ' (copie)',
            'slug'              => Game::generateSlug($original->name . ' copie'),
            'description'       => $original->description,
            'type'              => $original->type,
            'status'            => 'draft',
            'eligibility'       => $original->eligibility,
            'start_date'        => null,
            'end_date'          => null,
            'max_participants'  => $original->max_participants,
            'thank_you_message' => $original->thank_you_message,
        ]);

        foreach ($original->questions as $question) {
            GameQuestion::create([
                'game_id'        => $newGame->id,
                'order'          => $question->order,
                'text'           => $question->text,
                'type'           => $question->type,
                'options'        => $question->options,
                'correct_answer' => $question->correct_answer,
            ]);
        }

        return redirect()->route('gamification.edit', $newGame->slug)
            ->with('success', 'Jeu dupliqué avec ' . $original->questions->count() . ' question(s). Modifiez les informations puis activez-le.');
    }

    // ── Validation manuelle des réponses ──────────────────────────────────────

    public function markAnswer(Request $request, string $slug, int $answerId)
    {
        $game = Game::where('slug', $slug)->firstOrFail();

        // Vérifier que la réponse appartient bien à ce jeu (sécurité)
        $answer = GameAnswer::whereHas(
            'participation',
            fn($q) => $q->where('game_id', $game->id)
        )->findOrFail($answerId);

        $answer->update([
            'is_correct' => filter_var($request->input('is_correct'), FILTER_VALIDATE_BOOLEAN),
        ]);

        return back()->with('success', 'Réponse mise à jour.');
    }

    // ── Classement ────────────────────────────────────────────────────────────

    public function leaderboard(string $slug)
    {
        $game = Game::where('slug', $slug)->with('questions')->firstOrFail();

        $totalQuestions = $game->questions->count();

        $rankings = $game->participations()
            ->where('status', 'completed')
            ->with('answers')
            ->get()
            ->map(function ($p) use ($totalQuestions) {
                $correct = $p->answers->where('is_correct', true)->count();
                return [
                    'participation' => $p,
                    'correct'       => $correct,
                    'total'         => $totalQuestions,
                    'score'         => $totalQuestions > 0 ? round($correct / $totalQuestions * 100) : 0,
                ];
            })
            ->sort(function ($a, $b) {
                // Tri : score décroissant, puis temps de complétion croissant (plus rapide = mieux)
                if ($b['score'] !== $a['score']) {
                    return $b['score'] - $a['score'];
                }
                return $a['participation']->completed_at <=> $b['participation']->completed_at;
            })
            ->values();

        return view('gamification.leaderboard', compact('game', 'rankings', 'totalQuestions'));
    }

    // ── Flow Studio ───────────────────────────────────────────────────────────

    public function showFlow(string $slug)
    {
        $game = Game::where('slug', $slug)->with('questions')->firstOrFail();
        $flow = $this->flowGenerator->generate($game);

        $game->update(['synced_at' => now()]);

        $webhookBase = rtrim(config('app.url'), '/') . "/gamification/webhook/{$slug}";

        return view('gamification.flow', compact('game', 'flow', 'webhookBase'));
    }

    // ── Export CSV ────────────────────────────────────────────────────────────

    public function export(string $slug): StreamedResponse
    {
        $game = Game::where('slug', $slug)
            ->with(['questions', 'participations.answers.question'])
            ->firstOrFail();

        $filename = "participations-{$slug}-" . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($game) {
            $handle = fopen('php://output', 'w');

            $headers = ['Nom', 'Téléphone', 'Statut', 'Début', 'Fin'];
            foreach ($game->questions as $question) {
                $headers[] = "Q{$question->order}: " . mb_substr($question->text, 0, 50);
                $headers[] = "Q{$question->order} Correct";
            }
            fputcsv($handle, $headers, ';');

            foreach ($game->participations as $participation) {
                $row = [
                    $participation->participant_name ?? '',
                    $participation->phone_number,
                    $participation->status,
                    $participation->started_at?->format('d/m/Y H:i'),
                    $participation->completed_at?->format('d/m/Y H:i') ?? '',
                ];

                foreach ($game->questions as $question) {
                    $answer = $participation->answers->firstWhere('question_id', $question->id);
                    $row[]  = $answer?->answer_text ?? '';
                    $row[]  = $answer?->is_correct === true ? 'Oui' : ($answer?->is_correct === false ? 'Non' : '');
                }

                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
