<?php

namespace App\Http\Controllers;

use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LabelController extends Controller
{
    public function __construct(
        private ChatwootClient $chatwoot,
    ) {}

    /**
     * AJAX — Lister tous les labels du compte
     * GET /ajax/labels
     */
    public function list(): JsonResponse
    {
        $labels = $this->chatwoot->listAccountLabels();

        return response()->json($labels);
    }

    /**
     * AJAX — Créer un label
     * POST /ajax/labels
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title'       => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'color'       => 'nullable|string|max:7',
        ]);

        $result = $this->chatwoot->createAccountLabel(
            $request->title,
            $request->description,
            $request->color,
        );

        return response()->json($result, 201);
    }

    /**
     * AJAX — Labels d'une conversation
     * GET /ajax/conversations/{id}/labels
     */
    public function conversationLabels(int $conversationId): JsonResponse
    {
        $labels = $this->chatwoot->getConversationLabels($conversationId);

        return response()->json($labels);
    }

    /**
     * AJAX — Mettre à jour les labels d'une conversation
     * POST /ajax/conversations/{id}/labels
     */
    public function updateConversationLabels(int $conversationId, Request $request): JsonResponse
    {
        $request->validate([
            'labels'   => 'present|array',
            'labels.*' => 'string',
        ]);

        $result = $this->chatwoot->updateConversationLabels($conversationId, $request->labels);

        return response()->json($result);
    }
}
