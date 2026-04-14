<?php

namespace App\Services\Chatwoot;

use App\DTOs\ContactDTO;

class ContactService
{
    public function __construct(
        private ChatwootClient $client
    ) {}

    /**
     * Rechercher des contacts
     */
    public function search(string $query): array
    {
        $response = $this->client->searchContacts($query);
        $contacts = $response['payload'] ?? [];

        return array_map(fn(array $c) => ContactDTO::fromArray($c), $contacts);
    }

    /**
     * Trouver un contact par telephone ou le creer
     */
    public function findOrCreate(string $phone, string $name, ?string $email = null): ContactDTO
    {
        $results = $this->search($phone);

        if (count($results) > 0) {
            return $results[0];
        }

        $response = $this->client->createContact($name, $phone, $email);
        $contact = $response['payload']['contact'] ?? $response['payload'] ?? $response;

        return ContactDTO::fromArray($contact);
    }

    /**
     * Details d'un contact
     */
    public function get(int $contactId): ContactDTO
    {
        $response = $this->client->getContact($contactId);
        $contact = $response['payload'] ?? $response;

        return ContactDTO::fromArray($contact);
    }
}
