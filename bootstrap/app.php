<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
   ->withRouting(
      web: __DIR__.'/../routes/web.php',
      api: __DIR__.'/../routes/api.php',
      commands: __DIR__.'/../routes/console.php',
      health: '/up',
      // ↓ ajoute cette ligne
      then: function () {
          Route::middleware('web')
              ->group(base_path('routes/bot-tracking.php'));
          Route::middleware('web')
              ->group(base_path('routes/gamification.php'));
      },
  )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin'           => \App\Http\Middleware\AdminOnly::class,
            'role'            => \App\Http\Middleware\CheckRole::class,
            'twilio.webhook'  => \App\Http\Middleware\VerifyTwilioWebhook::class,
        ]);
        // Exclure les webhooks Twilio de la vérification CSRF
        // (Twilio envoie des POST sans token CSRF)
        $middleware->validateCsrfTokens(except: [
            'bot-tracking/twilio/*',
            'bot-tracking/webhooks/*',
            'gamification/webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();