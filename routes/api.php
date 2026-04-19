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

// Vérification escalade active pour un sender (appelé par n8n avant l'IA)
Route::get('/kash/escalade-active', [KashWebhookController::class, 'checkEscalade'])
    ->name('api.kash.escalade.check');

// Vérification réclamation active pour un sender
Route::get('/kash/reclamation-active', [KashWebhookController::class, 'checkReclamation'])
    ->name('api.kash.reclamation.check');

// Profil client persistant (identifiant connu depuis les tickets précédents)
Route::get('/kash/client-profil', [KashWebhookController::class, 'clientProfil'])
    ->name('api.kash.client.profil');

// Vérification unifiée : le bot doit-il répondre ?
// bot_actif=false → ticket ouvert, bot silencieux (forward to support)
// bot_actif=true  → aucun ticket ouvert (ou tous résolus), bot répond
Route::get('/kash/bot-actif', [KashWebhookController::class, 'checkBotActif'])
    ->name('api.kash.bot.actif');

// Log des messages bot ↔ client (historique conversations)
Route::post('/kash/messages', [KashWebhookController::class, 'logMessage'])
    ->name('api.kash.messages.log');

// Transfert message client vers Chatwoot quand escalade active
Route::post('/kash/forward-to-support', [KashWebhookController::class, 'forwardToSupport'])
    ->name('api.kash.forward');
