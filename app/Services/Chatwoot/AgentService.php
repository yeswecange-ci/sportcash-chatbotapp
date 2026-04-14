<?php

namespace App\Services\Chatwoot;

class AgentService
{
    public function __construct(
        private ChatwootClient $client
    ) {}

    /**
     * Lister tous les agents du compte
     */
    public function list(): array
    {
        return $this->client->listAgents();
    }

    /**
     * Trouver un agent par son ID
     */
    public function find(int $agentId): ?array
    {
        $agents = $this->list();

        foreach ($agents as $agent) {
            if (($agent['id'] ?? null) === $agentId) {
                return $agent;
            }
        }

        return null;
    }

    /**
     * Lister les agents disponibles (online)
     */
    public function available(): array
    {
        $agents = $this->list();

        return array_filter($agents, fn(array $agent) =>
            ($agent['availability_status'] ?? '') === 'online'
        );
    }
}
