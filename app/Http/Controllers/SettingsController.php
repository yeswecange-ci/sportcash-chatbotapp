<?php

namespace App\Http\Controllers;

use App\Services\Chatwoot\ChatwootClient;
use App\Services\Twilio\TwilioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Page de configuration système
     * GET /settings
     */
    public function index()
    {
        $config = [
            'chatwoot' => [
                'base_url'          => config('chatwoot.base_url', ''),
                'account_id'        => config('chatwoot.account_id', ''),
                'api_token'         => config('chatwoot.api_token', ''),
                'whatsapp_inbox_id' => config('chatwoot.whatsapp_inbox_id', ''),
                'polling_interval'  => config('chatwoot.polling_interval', 4000),
                'webhook_secret'    => config('chatwoot.webhook_secret', ''),
            ],
            'twilio' => [
                'sid'            => config('services.twilio.sid', ''),
                'auth_token'     => config('services.twilio.auth_token', ''),
                'whatsapp_from'  => config('services.twilio.whatsapp_from', ''),
            ],
            'app' => [
                'name'     => config('app.name', ''),
                'url'      => config('app.url', ''),
                'env'      => config('app.env', 'production'),
                'debug'    => config('app.debug', false),
                'timezone' => config('app.timezone', 'UTC'),
            ],
            'database' => [
                'connection' => config('database.default', 'mysql'),
                'host'       => config('database.connections.' . config('database.default') . '.host', ''),
                'port'       => config('database.connections.' . config('database.default') . '.port', ''),
                'database'   => config('database.connections.' . config('database.default') . '.database', ''),
                'username'   => config('database.connections.' . config('database.default') . '.username', ''),
            ],
        ];

        return view('settings.index', compact('config'));
    }

    /**
     * Mettre à jour des valeurs .env
     * POST /ajax/settings/update
     */
    public function update(Request $request): JsonResponse
    {
        $allowed = [
            // Chatwoot
            'CHATWOOT_BASE_URL', 'CHATWOOT_ACCOUNT_ID', 'CHATWOOT_API_TOKEN',
            'CHATWOOT_WHATSAPP_INBOX_ID', 'CHATWOOT_POLLING_INTERVAL', 'CHATWOOT_WEBHOOK_SECRET',
            // Twilio
            'TWILIO_SID', 'TWILIO_AUTH_TOKEN', 'TWILIO_WHATSAPP_FROM',
            // App
            'APP_NAME', 'APP_URL', 'APP_ENV', 'APP_DEBUG', 'APP_TIMEZONE',
        ];

        $values = $request->only($allowed);

        if (empty($values)) {
            return response()->json(['error' => 'Aucune valeur à mettre à jour'], 422);
        }

        try {
            foreach ($values as $key => $value) {
                $this->updateEnvValue($key, (string) $value);
            }

            // Invalider le cache de config
            \Artisan::call('config:clear');

            return response()->json(['ok' => true, 'message' => 'Configuration mise à jour']);
        } catch (\Exception $e) {
            Log::error('[Settings] Erreur mise à jour .env', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Tester la connexion Chatwoot
     * POST /ajax/settings/test-chatwoot
     */
    public function testChatwoot(): JsonResponse
    {
        try {
            $client = app(ChatwootClient::class);
            $start  = microtime(true);
            $client->getConversationCounts();
            $latency = (int) ((microtime(true) - $start) * 1000);

            return response()->json(['ok' => true, 'latency_ms' => $latency]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Tester la connexion Twilio
     * POST /ajax/settings/test-twilio
     */
    public function testTwilio(): JsonResponse
    {
        try {
            $twilio = app(TwilioService::class);

            if (!$twilio->isConfigured()) {
                return response()->json(['ok' => false, 'error' => 'Twilio non configuré (SID ou token manquant)']);
            }

            $start = microtime(true);
            $twilio->ping();
            $latency = (int) ((microtime(true) - $start) * 1000);

            return response()->json(['ok' => true, 'latency_ms' => $latency]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Tester la connexion base de données
     * POST /ajax/settings/test-database
     */
    public function testDatabase(): JsonResponse
    {
        try {
            $start = microtime(true);
            DB::statement('SELECT 1');
            $latency = (int) ((microtime(true) - $start) * 1000);

            return response()->json(['ok' => true, 'latency_ms' => $latency]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Récupérer les dernières lignes du log
     * GET /ajax/settings/logs
     */
    public function getLogs(Request $request): JsonResponse
    {
        $logFile = storage_path('logs/laravel.log');
        $lines   = (int) $request->get('lines', 200);
        $lines   = max(50, min(500, $lines));

        if (!file_exists($logFile)) {
            return response()->json(['lines' => [], 'size' => 0]);
        }

        $all    = file($logFile, FILE_IGNORE_NEW_LINES) ?: [];
        $subset = array_slice($all, -$lines);
        $size   = filesize($logFile);

        return response()->json(['lines' => $subset, 'size' => $size]);
    }

    /**
     * Vider le fichier de log
     * DELETE /ajax/settings/logs
     */
    public function clearLogs(): JsonResponse
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            file_put_contents($logFile, '');

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Met à jour (ou crée) une clé dans le fichier .env
     */
    private function updateEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            throw new \RuntimeException('.env introuvable');
        }

        // Encadrer de guillemets si la valeur contient des espaces ou caractères spéciaux
        $escapedValue = preg_match('/[\s#"\'\\\\]/', $value) ? '"' . addslashes($value) . '"' : $value;

        $content = file_get_contents($envPath);

        // Remplacer la ligne existante ou l'ajouter en fin de fichier
        if (preg_match("/^{$key}=.*/m", $content)) {
            $content = preg_replace("/^{$key}=.*/m", "{$key}={$escapedValue}", $content);
        } else {
            $content .= PHP_EOL . "{$key}={$escapedValue}";
        }

        file_put_contents($envPath, $content);
    }
}
