<?php

namespace App\Services\Twilio;

use Twilio\Rest\Client;
use Twilio\Http\CurlClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TwilioService
{
    private ?Client $client = null;
    private ?string $from = null;

    public function __construct()
    {
        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.auth_token');
        $this->from = config('services.twilio.whatsapp_from');

        if ($sid && $token) {
            $this->client = new Client($sid, $token);
            if (!app()->isProduction()) {
                $this->client->setHttpClient(new CurlClient([
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => 0,
                ]));
            }
        }
    }

    /**
     * Vérifie si Twilio est configuré
     */
    public function isConfigured(): bool
    {
        return $this->client !== null && $this->from;
    }

    /**
     * Envoyer un message WhatsApp via Twilio
     */
    public function sendWhatsApp(string $to, string $body, array $mediaUrls = []): void
    {
        if (!str_starts_with($to, 'whatsapp:')) {
            $to = "whatsapp:{$to}";
        }

        $params = ['from' => $this->from, 'body' => $body];

        if (!empty($mediaUrls)) {
            $params['mediaUrl'] = $mediaUrls;
        }

        try {
            $this->client->messages->create($to, $params);

            Log::info('WhatsApp envoyé', [
                'to'         => $to,
                'body'       => mb_substr($body, 0, 50),
                'media_count' => count($mediaUrls),
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur envoi WhatsApp', [
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Récupérer les templates WhatsApp depuis Twilio Content API
     * Résultat mis en cache 5 minutes
     */
    public function getContentTemplates(): array
    {
        return Cache::remember('twilio_content_templates', 300, function () {
            $contents = $this->client->content->v1->contents->read();

            return collect($contents)->map(function ($content) {
                $types = $content->types ?? [];
                $body = '';

                // Extraire le body depuis les différents types de template
                if (isset($types['twilio/text'])) {
                    $body = $types['twilio/text']['body'] ?? '';
                } elseif (isset($types['twilio/quick-reply'])) {
                    $body = $types['twilio/quick-reply']['body'] ?? '';
                } elseif (isset($types['twilio/card'])) {
                    $body = $types['twilio/card']['body'] ?? '';
                }

                // Parser les placeholders {{1}}, {{2}}, etc.
                preg_match_all('/\{\{(\d+)\}\}/', $body, $matches);
                $variables = $matches[1] ?? [];

                return [
                    'sid'           => $content->sid,
                    'friendly_name' => $content->friendlyName,
                    'body'          => $body,
                    'variables'     => $variables,
                ];
            })->all();
        });
    }

    /**
     * Tester la connexion Twilio (appel API léger)
     */
    public function ping(): void
    {
        // Appel minimal : récupération du compte courant
        $this->client->api->v2010->accounts->read(['limit' => 1]);
    }

    /**
     * Récupérer le statut d'un message Twilio par son SID
     */
    public function fetchMessage(string $sid): object
    {
        return $this->client->messages($sid)->fetch();
    }

    /**
     * Envoyer un message template WhatsApp via Twilio avec StatusCallback
     */
    public function sendTemplateWithCallback(string $to, string $contentSid, array $variables = [], ?string $statusCallbackUrl = null): object
    {
        if (!str_starts_with($to, 'whatsapp:')) {
            $to = "whatsapp:{$to}";
        }

        $params = [
            'from'       => $this->from,
            'contentSid' => $contentSid,
        ];

        if (!empty($variables)) {
            $params['contentVariables'] = json_encode($variables);
        }

        if ($statusCallbackUrl) {
            $params['statusCallback'] = $statusCallbackUrl;
        }

        try {
            $message = $this->client->messages->create($to, $params);

            Log::info('WhatsApp template envoyé avec callback', [
                'to'         => $to,
                'contentSid' => $contentSid,
                'sid'        => $message->sid,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('Erreur envoi template WhatsApp', [
                'to'         => $to,
                'contentSid' => $contentSid,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Envoyer un message template WhatsApp via Twilio
     */
    public function sendTemplate(string $to, string $contentSid, array $variables = []): object
    {
        if (!str_starts_with($to, 'whatsapp:')) {
            $to = "whatsapp:{$to}";
        }

        $params = [
            'from'       => $this->from,
            'contentSid' => $contentSid,
        ];

        if (!empty($variables)) {
            $params['contentVariables'] = json_encode($variables);
        }

        try {
            $message = $this->client->messages->create($to, $params);

            Log::info('WhatsApp template envoyé', [
                'to'         => $to,
                'contentSid' => $contentSid,
                'sid'        => $message->sid,
            ]);

            return $message;
        } catch (\Exception $e) {
            Log::error('Erreur envoi template WhatsApp', [
                'to'         => $to,
                'contentSid' => $contentSid,
                'error'      => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
