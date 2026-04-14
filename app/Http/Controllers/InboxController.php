<?php

namespace App\Http\Controllers;

use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InboxController extends Controller
{
    public function __construct(
        private ChatwootClient $chatwoot,
    ) {}

    /**
     * AJAX — Get inbox settings
     */
    public function getSettings(int $inboxId): JsonResponse
    {
        try {
            $inbox = $this->chatwoot->getInbox($inboxId);
            return response()->json([
                'success' => true,
                'inbox'   => $inbox,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Toggle auto-assignment (round-robin)
     */
    public function updateAutoAssignment(int $inboxId, Request $request): JsonResponse
    {
        $request->validate([
            'auto_assignment' => 'required|boolean',
        ]);

        try {
            $inbox = $this->chatwoot->updateInbox($inboxId, [
                'channel' => [
                    'auto_assignment' => $request->boolean('auto_assignment'),
                ],
            ]);
            return response()->json([
                'success'         => true,
                'auto_assignment' => $request->boolean('auto_assignment'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — List all inboxes
     */
    public function list(): JsonResponse
    {
        try {
            $inboxes = $this->chatwoot->listInboxes();
            return response()->json($inboxes['payload'] ?? $inboxes);
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}
