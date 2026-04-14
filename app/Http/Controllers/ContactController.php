<?php

namespace App\Http\Controllers;

use App\Services\Chatwoot\ChatwootClient;
use App\Services\Twilio\TwilioService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    // TwilioService injecté en lazy via app() pour éviter un crash
    // si Twilio n'est pas configuré sur le serveur
    public function __construct(
        private ChatwootClient $chatwoot,
    ) {}

    private function twilio(): TwilioService
    {
        return app(TwilioService::class);
    }

    /**
     * Page contacts
     * GET /contacts
     */
    public function index(Request $request)
    {
        $page    = max(1, min((int) $request->get('page', 1), 100));
        $search  = $request->get('q');
        $perPage = 15;

        try {
            if ($search) {
                $data     = $this->chatwoot->searchContacts($search);
                $contacts = $data['payload'] ?? [];
                $meta     = ['total' => count($contacts), 'current_page' => 1, 'pages' => 1];
            } else {
                $data     = $this->chatwoot->listContacts($page, '-last_activity_at');
                $contacts = $data['payload'] ?? [];
                $rawMeta  = $data['meta'] ?? [];
                $total    = $rawMeta['count'] ?? $rawMeta['total'] ?? count($contacts);
                $meta     = [
                    'total'        => $total,
                    'current_page' => $rawMeta['current_page'] ?? $page,
                    'pages'        => $total > 0 ? (int) ceil($total / $perPage) : 1,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('[Contacts] API unavailable', ['error' => $e->getMessage()]);
            $contacts = [];
            $meta     = ['total' => 0, 'current_page' => 1, 'pages' => 1];
        }

        return view('contacts.index', [
            'contacts'      => $contacts,
            'meta'          => $meta,
            'currentPage'   => $page,
            'currentSearch' => $search,
        ]);
    }

    /**
     * AJAX — Recherche contacts
     * GET /ajax/contacts/search
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json(['payload' => []]);
        }

        try {
            $data = $this->chatwoot->searchContacts($q);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['payload' => []], 500);
        }
    }

    /**
     * AJAX — Creer un contact
     * POST /ajax/contacts
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'required|string|max:255',
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:30',
        ]);

        try {
            $result = $this->chatwoot->createContact(
                $request->name,
                $request->phone_number ?? '',
                $request->email
            );
            return response()->json($result, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Details complets d'un contact
     * GET /ajax/contacts/{id}
     */
    public function show(int $contactId): JsonResponse
    {
        $contact = $this->chatwoot->getContact($contactId);

        return response()->json($contact);
    }

    /**
     * AJAX — Modifier un contact
     * PUT /ajax/contacts/{id}
     */
    public function update(int $contactId, Request $request): JsonResponse
    {
        $request->validate([
            'name'         => 'sometimes|string|max:255',
            'email'        => 'sometimes|nullable|email|max:255',
            'phone_number' => 'sometimes|nullable|string|max:30',
            'company_name' => 'sometimes|nullable|string|max:255',
        ]);

        $result = $this->chatwoot->updateContact($contactId, $request->only([
            'name', 'email', 'phone_number', 'company_name',
        ]));

        return response()->json($result);
    }

    /**
     * AJAX — Supprimer un contact
     * DELETE /ajax/contacts/{id}
     */
    public function destroy(int $contactId): JsonResponse
    {
        try {
            $this->chatwoot->deleteContact($contactId);
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Conversations du contact
     * GET /ajax/contacts/{id}/conversations
     */
    public function conversations(int $contactId): JsonResponse
    {
        $data = $this->chatwoot->getContactConversations($contactId);

        return response()->json($data);
    }

    /**
     * AJAX — Notes du contact
     * GET /ajax/contacts/{id}/notes
     */
    public function notes(int $contactId): JsonResponse
    {
        $notes = $this->chatwoot->listContactNotes($contactId);

        return response()->json($notes);
    }

    /**
     * AJAX — Ajouter une note
     * POST /ajax/contacts/{id}/notes
     */
    public function storeNote(int $contactId, Request $request): JsonResponse
    {
        $request->validate(['content' => 'required|string|max:4096']);

        $result = $this->chatwoot->createContactNote($contactId, $request->content);

        return response()->json($result, 201);
    }

    /**
     * AJAX — Envoyer un template WhatsApp à un contact
     * POST /ajax/contacts/{id}/send-template
     */
    public function sendTemplate(int $contactId, Request $request): JsonResponse
    {
        $request->validate([
            'content_sid'   => 'required|string|starts_with:HX',
            'variables'     => 'nullable|array',
            'template_name' => 'nullable|string',
        ]);

        if (!$this->twilio()->isConfigured()) {
            return response()->json(['error' => 'Twilio non configuré'], 500);
        }

        try {
            $contact = $this->chatwoot->getContact($contactId);
            $phone = $contact['phone_number'] ?? null;

            if (!$phone) {
                return response()->json(['error' => 'Ce contact n\'a pas de numéro de téléphone'], 422);
            }

            $variables = $request->input('variables', []);
            $this->twilio()->sendTemplate($phone, $request->content_sid, $variables);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * AJAX — Supprimer une note
     * DELETE /ajax/contacts/{id}/notes/{noteId}
     */
    public function destroyNote(int $contactId, int $noteId): JsonResponse
    {
        $this->chatwoot->deleteContactNote($contactId, $noteId);

        return response()->json(['success' => true]);
    }
}
