<?php

namespace App\Http\Controllers\Gamification;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\Gamification\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(private GamificationService $service) {}

    /**
     * POST /gamification/webhook/{slug}/check
     * Vérifie l'éligibilité d'un participant (appelé par Twilio Flow).
     */
    public function check(Request $request, string $slug): JsonResponse
    {
        $phone = $request->input('From') ?? $request->input('phone', '');
        $phone = $this->normalizePhone($phone);

        Log::info("[Gamification] check: slug={$slug} phone={$phone}");

        $result = $this->service->checkParticipant($slug, $phone);

        // Si eligible, créer la participation dès maintenant
        if ($result['eligible'] === 'true') {
            $game = Game::where('slug', $slug)->first();
            if ($game) {
                $name = $result['name_known'] === 'true' ? $result['name'] : null;
                $this->service->startParticipation($game->id, $phone, $name);
            }
        }

        return response()->json($result);
    }

    /**
     * POST /gamification/webhook/{slug}/save-name
     * Enregistre le nom du participant (demandé dans le flow).
     */
    public function saveName(Request $request, string $slug): JsonResponse
    {
        $phone = $this->normalizePhone($request->input('From') ?? $request->input('phone', ''));
        $name  = $request->input('name', '');

        $game = Game::where('slug', $slug)->first();
        if ($game && $name) {
            $this->service->saveName($game->id, $phone, $name);
        }

        return response()->json(['success' => 'true']);
    }

    /**
     * POST /gamification/webhook/{slug}/answer
     * Soumet une réponse à une question.
     */
    public function answer(Request $request, string $slug): JsonResponse
    {
        $phone         = $this->normalizePhone($request->input('From') ?? $request->input('phone', ''));
        $questionOrder = (int) $request->input('question_order', 1);
        $answer        = $request->input('answer', '');

        $game = Game::where('slug', $slug)->first();
        if ($game) {
            $this->service->saveAnswer($game->id, $phone, $questionOrder, $answer);
        }

        return response()->json(['success' => 'true']);
    }

    /**
     * POST /gamification/webhook/{slug}/complete
     * Marque la participation comme complète.
     */
    public function complete(Request $request, string $slug): JsonResponse
    {
        $phone = $this->normalizePhone($request->input('From') ?? $request->input('phone', ''));

        $game = Game::where('slug', $slug)->first();
        if ($game) {
            $this->service->completeParticipation($game->id, $phone);
        }

        return response()->json(['success' => 'true']);
    }

    private function normalizePhone(string $phone): string
    {
        // Twilio envoie "whatsapp:+2250XXXXXXX" — on garde le numéro brut
        return str_replace('whatsapp:', '', $phone);
    }
}
