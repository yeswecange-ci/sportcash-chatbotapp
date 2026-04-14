<?php

namespace App\Services\Chatwoot;

class MessageService
{
    public function __construct(
        private ChatwootClient $client
    ) {}

    /**
     * Envoyer un message au client (visible sur WhatsApp)
     */
    public function sendToCustomer(int $conversationId, string $content, array $attachments = []): array
    {
        if (!empty($attachments)) {
            return $this->client->sendMessageWithAttachments($conversationId, $content ?: null, $attachments, false, 'outgoing');
        }

        return $this->client->sendMessage($conversationId, $content, false, 'outgoing');
    }

    /**
     * Envoyer une note interne (visible agents uniquement)
     */
    public function sendPrivateNote(int $conversationId, string $content, array $attachments = []): array
    {
        if (!empty($attachments)) {
            return $this->client->sendMessageWithAttachments($conversationId, $content ?: null, $attachments, true);
        }

        return $this->client->sendMessage($conversationId, $content, true);
    }
}
