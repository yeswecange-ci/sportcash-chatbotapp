<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ChatwootDebug extends Command
{
    protected $signature = 'chatwoot:debug';
    protected $description = 'Debug la connexion Chatwoot avec détails erreur';

    public function handle(): int
    {
        $url   = config('chatwoot.base_url');
        $token = config('chatwoot.api_token');
        $id    = config('chatwoot.account_id');

        $this->info("URL: {$url}");
        $this->info("Account ID: {$id}");
        $this->info("Token (5 premiers): " . substr($token, 0, 5) . '...');
        $this->newLine();

        try {
            $response = Http::withHeaders([
                    'api_access_token' => $token,
                ])
                ->withoutVerifying()  // Ignore SSL pour le debug
                ->timeout(15)
                ->get("{$url}/api/v1/accounts/{$id}/agents");

            $this->info("Status HTTP: " . $response->status());
            $this->info("Réponse: " . substr($response->body(), 0, 500));

        } catch (\Exception $e) {
            $this->error("Exception: " . get_class($e));
            $this->error("Message: " . $e->getMessage());
        }

        return self::SUCCESS;
    }
}