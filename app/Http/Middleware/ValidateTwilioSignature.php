<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Security\RequestValidator;

class ValidateTwilioSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $authToken = config('services.twilio.auth_token');

        if (! $authToken) {
            return response()->json(['error' => 'Twilio auth token not configured'], 500);
        }

        $validator = new RequestValidator($authToken);

        $signature = $request->header('X-Twilio-Signature', '');
        $url = $request->fullUrl();
        $params = $request->isJson() ? [] : $request->all();

        if (! $validator->validate($signature, $url, $params)) {
            return response()->json(['error' => 'Invalid Twilio signature'], 403);
        }

        return $next($request);
    }
}
