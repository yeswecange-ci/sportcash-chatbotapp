<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyTwilioWebhook
{
    /**
     * Vérifie la signature HMAC-SHA1 envoyée par Twilio dans X-Twilio-Signature.
     * Si TWILIO_AUTH_TOKEN n'est pas configuré, la vérification est ignorée (utile en dev/test).
     *
     * Algorithme officiel Twilio :
     * 1. Prendre l'URL complète de la requête
     * 2. Trier les paramètres POST alphabétiquement
     * 3. Concaténer url + clé + valeur pour chaque paramètre
     * 4. Signer avec HMAC-SHA1 et comparer au header
     *
     * @see https://www.twilio.com/docs/usage/webhooks/webhooks-security
     */
    public function handle(Request $request, Closure $next)
    {
        $authToken = config('services.twilio.auth_token');

        // Pas de token configuré → on laisse passer (environnement dev / test local)
        if (!$authToken) {
            return $next($request);
        }

        $signature = $request->header('X-Twilio-Signature', '');

        if (!$signature) {
            return response()->json(['error' => 'Missing Twilio signature'], 403);
        }

        // Construction de la chaîne à signer
        $url    = $request->url();
        $params = $request->post() ?? [];
        ksort($params);

        $data = $url . implode('', array_map(
            fn($k, $v) => $k . $v,
            array_keys($params),
            array_values($params)
        ));

        $computed = base64_encode(hash_hmac('sha1', $data, $authToken, true));

        if (!hash_equals($computed, $signature)) {
            return response()->json(['error' => 'Invalid Twilio signature'], 403);
        }

        return $next($request);
    }
}
