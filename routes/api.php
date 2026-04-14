<?php

use App\Http\Controllers\Webhook\TwilioWebhookController;
use App\Http\Controllers\Webhook\ChatwootWebhookController;
use App\Http\Controllers\Webhook\KashWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Webhooks
|--------------------------------------------------------------------------
|
| Ces routes ne nécessitent PAS l'auth Laravel.
| Elles sont protégées par la signature Twilio et le secret Chatwoot.
|
*/

Route::prefix('webhooks')->group(function () {

    // Twilio Studio → Handoff vers Chatwoot
    Route::post('/twilio/handoff', [TwilioWebhookController::class, 'handoff'])
        ->middleware(\App\Http\Middleware\ValidateTwilioSignature::class)
        ->name('webhook.twilio.handoff');

    // Chatwoot → Agent message → Twilio WhatsApp
    Route::post('/chatwoot', [ChatwootWebhookController::class, 'handle'])
        ->name('webhook.chatwoot');
});

// ── Twilio StatusCallback (sans signature check pour dev) ──
Route::post('/twilio/status', [TwilioWebhookController::class, 'statusCallback'])
    ->name('webhook.twilio.status');

// ── Kash Bot — Signaux n8n ──────────────────────────────────────────────────
// POST /api/reclamations  ← signal [[RECLAMATION]] depuis n8n
// POST /api/escalades     ← signal [[ESCALADE]] depuis n8n
// Sécurisé via header X-Bot-Secret (configurer BOT_WEBHOOK_SECRET dans .env)
Route::post('/reclamations', [KashWebhookController::class, 'reclamation'])
    ->name('api.kash.reclamation');

Route::post('/escalades', [KashWebhookController::class, 'escalade'])
    ->name('api.kash.escalade');
