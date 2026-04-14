<?php

namespace App\Console\Commands;

use App\Services\Chatwoot\ChatwootClient;
use Illuminate\Console\Command;

class ChatwootTestConnection extends Command
{
    protected $signature = 'chatwoot:test';
    protected $description = 'Tester la connexion à l\'API Chatwoot';

    public function handle(ChatwootClient $client): int
    {
        $this->info('🔌 Test de connexion à Chatwoot...');
        $this->info('   URL: ' . config('chatwoot.base_url'));
        $this->info('   Account ID: ' . config('chatwoot.account_id'));
        $this->newLine();

        // Test 1 : Ping API
        $this->info('1️⃣  Test API...');
        if (!$client->ping()) {
            $this->error('❌ Impossible de se connecter à Chatwoot. Vérifiez CHATWOOT_BASE_URL et CHATWOOT_API_TOKEN.');
            return self::FAILURE;
        }
        $this->info('   ✅ Connexion OK');

        // Test 2 : Lister les agents
        $this->info('2️⃣  Récupération des agents...');
        try {
            $agents = $client->listAgents();
            $this->info('   ✅ ' . count($agents) . ' agent(s) trouvé(s)');
            foreach ($agents as $agent) {
                $this->line("      - {$agent['name']} ({$agent['email']}) [{$agent['role']}]");
            }
        } catch (\Exception $e) {
            $this->error('   ❌ ' . $e->getMessage());
        }

        // Test 3 : Lister les conversations
        $this->info('3️⃣  Récupération des conversations...');
        try {
            $conversations = $client->listConversations('open', 'all', 1);
            $meta = $conversations['data']['meta'] ?? [];
            $count = count($conversations['data']['payload'] ?? []);
            $this->info("   ✅ {$count} conversation(s) ouvertes récupérées");
            $this->line("      Mine: " . ($meta['mine_count'] ?? 0));
            $this->line("      Non assignées: " . ($meta['unassigned_count'] ?? 0));
            $this->line("      Assignées: " . ($meta['assigned_count'] ?? 0));
            $this->line("      Total: " . ($meta['all_count'] ?? 0));
        } catch (\Exception $e) {
            $this->error('   ❌ ' . $e->getMessage());
        }

        $this->newLine();
        $this->info('🎉 Test terminé ! Votre connexion Chatwoot fonctionne.');

        return self::SUCCESS;
    }
}
